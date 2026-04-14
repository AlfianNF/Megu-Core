<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GenerateModel extends Command
{
    protected $signature = 'generate:model {table?}';
    protected $description = 'Automated Model Generator with Lifecycle Hooks, Unique detection, and Hashing';

    public function handle()
    {
        $this->info("Checking Database...");
        $tableArgument = $this->argument('table');
        $driver = config('database.default');
        $dbName = config("database.connections." . $driver . ".database");

        $tables = $this->getTables($driver, $dbName, $tableArgument);

        foreach ($tables as $table) {
            $tableName = $table->table_name;
            $modelName = Str::studly($tableName);
            
            $this->comment("Processing: {$tableName} -> {$modelName}");

            $columns = $this->getColumns($driver, $dbName, $tableName);
            $uniques = $this->getUniqueIndexes($driver, $dbName, $tableName); // Ambil Unique Indexes
            
            $params = $this->prepareParams($tableName, $modelName, $columns, $uniques);

            $this->writeFile(
                app_path("Models/{$modelName}.php"),
                view('generate.model', $params)->render()
            );

            $this->info("Model {$modelName} Generated successfully!");
        }
    }

    private function getTables($driver, $dbName, $target) {
        $query = ($driver === 'pgsql') 
            ? "SELECT table_name AS table_name FROM information_schema.tables WHERE table_catalog = ? AND table_schema = 'public'"
            : "SELECT table_name AS table_name FROM information_schema.tables WHERE table_schema = ?";
        
        $exclude = ['migrations', 'failed_jobs', 'password_resets', 'personal_access_tokens', 'websockets_statistics_entries'];
        
        return collect(DB::select($query, [$dbName]))
                ->filter(fn($t) => !in_array($t->table_name, $exclude))
                ->filter(fn($t) => $target ? $t->table_name === $target : true);
    }

    private function prepareParams($tableName, $modelName, $columns, $uniques) {
        $data = [
            'table_name'      => $tableName,
            'studly_caps'     => $modelName,
            'fieldList'       => [],
            'fieldAdd'        => [],
            'fieldEdit'       => [],
            'fieldValidation' => [],
            'fieldRelation'   => [],
            'fieldUnique'     => [],
            'fieldUpload'     => [],
            'fileRoot'        => $tableName,
            'has_password'    => false,
        ];

        $ignore = ['id', 'created_at', 'updated_at', 'deleted_at'];

        // Ambil Unique Fields dari Database
        foreach ($uniques as $unique) {
            $data['fieldUnique'][] = explode(',', $unique->column_list);
        }

        foreach ($columns as $col) {
            $data['fieldList'][] = $col->column_name;

            // Deteksi Upload Field (img_, file_, doc_)
            if (Str::startsWith($col->column_name, ['img_', 'file_', 'doc_'])) {
                $data['fieldUpload'][] = $col->column_name;
            }

            if (!in_array($col->column_name, $ignore)) {
                $data['fieldAdd'][] = $col->column_name;
                $data['fieldEdit'][] = $col->column_name;

                // Validasi Dasar
                $rules = ($col->is_nullable === 'YES') ? 'nullable' : 'required';
                
                if (Str::contains($col->data_type, ['int', 'bigint', 'integer'])) $rules .= '|integer';
                if ($col->column_name === 'email') $rules .= '|email';
                if ($col->character_maximum_length) $rules .= '|max:' . $col->character_maximum_length;
                
                $data['fieldValidation'][$col->column_name] = $rules;
            }

            // Flag Password/Pin untuk Hashing
            if (in_array($col->column_name, ['password', 'pin'])) {
                $data['has_password'] = true;
            }

            // Relation detection
            if ($col->ref_table) {
                $data['fieldRelation'][$col->column_name] = [
                    'table' => $col->ref_table,
                    'field' => $col->ref_column,
                    'selectField' => $col->ref_column,
                    'alias' => 'rel_' . Str::replaceLast('_id', '', $col->column_name)
                ];
            }
        }
        return $data;
    }

    private function getUniqueIndexes($driver, $dbName, $tableName) {
        if ($driver === 'pgsql') {
            return DB::select("
                SELECT i.relname as index_name, string_agg(a.attname, ',') as column_list
                FROM pg_class t, pg_class i, pg_index ix, pg_attribute a, pg_namespace n
                WHERE t.oid = ix.indrelid AND i.oid = ix.indexrelid AND a.attrelid = t.oid
                AND a.attnum = ANY(ix.indkey) AND ix.indisprimary = false AND ix.indisunique = true
                AND t.relname = ? AND n.nspname = 'public'
                GROUP BY i.relname", [$tableName]);
        }
        
        return DB::select("
            SELECT index_name AS index_name, GROUP_CONCAT(column_name ORDER BY seq_in_index) AS column_list
            FROM information_schema.statistics
            WHERE table_schema = ? AND table_name = ? AND non_unique = 0 AND index_name <> 'PRIMARY'
            GROUP BY index_name", [$dbName, $tableName]);
    }

    private function getColumns($driver, $dbName, $table) {
        if ($driver === 'pgsql') {
            $sql = "SELECT a.column_name, a.data_type, a.character_maximum_length, a.is_nullable, 
                    b.primary_table as ref_table, b.pk_column as ref_column
                    FROM information_schema.columns a
                    LEFT JOIN (
                        SELECT kcu.table_name as foreign_table, rel_kcu.table_name as primary_table, 
                        kcu.column_name as fk_column, rel_kcu.column_name as pk_column
                        FROM information_schema.table_constraints tco
                        JOIN information_schema.key_column_usage kcu ON tco.constraint_name = kcu.constraint_name
                        JOIN information_schema.referential_constraints rco ON tco.constraint_name = rco.constraint_name
                        JOIN information_schema.key_column_usage rel_kcu ON rco.unique_constraint_name = rel_kcu.constraint_name
                        WHERE tco.constraint_type = 'FOREIGN KEY' AND kcu.table_name = ?
                    ) b ON a.column_name = b.fk_column WHERE a.table_catalog = ? AND a.table_name = ?";
            
            return DB::select($sql, [$table, $dbName, $table]);
        } else {
            $sql = "SELECT a.column_name AS column_name, a.data_type AS data_type, 
                    a.character_maximum_length AS character_maximum_length, a.is_nullable AS is_nullable,
                    b.REFERENCED_TABLE_NAME AS ref_table, b.REFERENCED_COLUMN_NAME AS ref_column
                    FROM information_schema.columns a
                    LEFT JOIN information_schema.KEY_COLUMN_USAGE b ON a.table_name = b.table_name 
                    AND a.column_name = b.column_name AND b.REFERENCED_TABLE_NAME IS NOT NULL
                    WHERE a.table_schema = ? AND a.table_name = ?";
            
            return DB::select($sql, [$dbName, $table]);
        }
    }

    private function writeFile($path, $content) {
        if (!is_dir(dirname($path))) mkdir(dirname($path), 0755, true);
        file_put_contents($path, "<?php\n\n" . $content);
    }
}