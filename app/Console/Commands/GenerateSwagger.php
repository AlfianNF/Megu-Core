<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use OpenApi\Annotations as OA;

class GenerateSwagger extends Command
{
    protected $signature = 'core:swagger';
    protected $description = 'Generate Swagger JSON dengan Response Lengkap dan Form Input yang Muncul';

    public function handle()
    {
        $this->info("Memulai proses generate dokumentasi...");

        $openapi = new OA\OpenApi([]);
        $openapi->info = new OA\Info([
            'title' => 'Megu-Core Auto API',
            'version' => '1.0.0',
            'description' => 'Dokumentasi API CRUD Otomatis & System Routes'
        ]);

        $openapi->servers = [
            new OA\Server(['url' => config('app.url') . '/api', 'description' => 'Local Server'])
        ];

        $openapi->components = new OA\Components([]);
        $openapi->components->securitySchemes = [
            'bearerAuth' => new OA\SecurityScheme([
                'securityScheme' => 'bearerAuth',
                'type' => 'http',
                'scheme' => 'bearer',
                'bearerFormat' => 'JWT'
            ])
        ];

        $paths = [];

        // --- LOGIN ---
        $loginProps = [
            'username' => new OA\Property(['property' => 'username', 'type' => 'string', 'example' => 'admin']),
            'password' => new OA\Property(['property' => 'password', 'type' => 'string', 'format' => 'password', 'example' => 'password123'])
        ];
        $loginPath = new OA\PathItem(['path' => '/login']);
        $loginPath->post = new OA\Post([
            'tags' => ['Auth'],
            'summary' => 'Login User',
            'requestBody' => $this->makeJsonRequestBody($loginProps, ['username', 'password']),
            'responses' => $this->makeCommonResponses(200, "Berhasil Login")
        ]);
        $paths['/login'] = $loginPath;

        // --- UPLOAD ---
        $uploadPath = new OA\PathItem(['path' => '/upload-tmp']);
        $uploadPath->post = new OA\Post([
            'tags' => ['Storage'],
            'summary' => 'Upload File',
            'security' => [['bearerAuth' => []]],
            'requestBody' => new OA\RequestBody([
                'required' => true,
                'content' => [
                    'multipart/form-data' => new OA\MediaType([
                        'mediaType' => 'multipart/form-data',
                        'schema' => new OA\Schema([
                            'type' => 'object',
                            'properties' => [
                                'file' => new OA\Property(['property' => 'file', 'type' => 'string', 'format' => 'binary'])
                            ]
                        ])
                    ])
                ]
            ]),
            'responses' => $this->makeCommonResponses(200, "Upload Berhasil")
        ]);
        $paths['/upload-tmp'] = $uploadPath;

        // --- DYNAMIC MODELS ---
        $modelFiles = File::files(app_path('Models'));
        foreach ($modelFiles as $file) {
            $modelName = str_replace('.php', '', $file->getFilename());
            $classModel = "\\App\\Models\\" . $modelName;

            if (!class_exists($classModel) || in_array($modelName, ["User", "Controller"])) continue;
            if (str_ends_with($modelName, '2')) continue;

            $slug = Str::lower($modelName);
            $this->line("🛠️  Processing: $modelName");

            $pathItem = new OA\PathItem(['path' => "/$slug"]);

            // GET LIST
            $pathItem->get = new OA\Get([
                'tags' => [$modelName],
                'summary' => "Get List $modelName",
                'security' => [['bearerAuth' => []]],
                'responses' => $this->makeCommonResponses()
            ]);

            // POST CREATE (Gunakan field sebagai Key agar jadi Object)
            $addFields = [];
            if (defined("$classModel::FIELD_ADD")) {
                foreach (constant("$classModel::FIELD_ADD") as $f) {
                    $addFields[$f] = new OA\Property(['property' => $f, 'type' => 'string']);
                }
            }
            $pathItem->post = new OA\Post([
                'tags' => [$modelName],
                'summary' => "Create $modelName",
                'security' => [['bearerAuth' => []]],
                'requestBody' => $this->makeJsonRequestBody($addFields),
                'responses' => $this->makeCommonResponses(201, "Data Created")
            ]);

            // PUT UPDATE
            $editFields = ['id' => new OA\Property(['property' => 'id', 'type' => 'integer'])];
            if (defined("$classModel::FIELD_EDIT")) {
                foreach (constant("$classModel::FIELD_EDIT") as $f) {
                    $editFields[$f] = new OA\Property(['property' => $f, 'type' => 'string']);
                }
            }
            $pathItem->put = new OA\Put([
                'tags' => [$modelName],
                'summary' => "Update $modelName",
                'security' => [['bearerAuth' => []]],
                'requestBody' => $this->makeJsonRequestBody($editFields),
                'responses' => $this->makeCommonResponses(200, "Data Updated")
            ]);

            // DELETE
            $pathItem->delete = new OA\Delete([
                'tags' => [$modelName],
                'summary' => "Delete $modelName",
                'security' => [['bearerAuth' => []]],
                'requestBody' => $this->makeJsonRequestBody(['id' => new OA\Property(['property' => 'id', 'type' => 'integer'])]),
                'responses' => $this->makeCommonResponses()
            ]);

            $paths["/$slug"] = $pathItem;

            // DETAIL PATH /{id}
            $detailPath = new OA\PathItem(['path' => "/$slug/{id}"]);
            $detailPath->get = new OA\Get([
                'tags' => [$modelName],
                'summary' => "Detail $modelName",
                'security' => [['bearerAuth' => []]],
                'parameters' => [
                    new OA\Parameter([
                        'name' => 'id', 'in' => 'path', 'required' => true,
                        'schema' => new OA\Schema(['type' => 'integer'])
                    ])
                ],
                'responses' => $this->makeCommonResponses(200, "Success", true)
            ]);
            $paths["/$slug/{id}"] = $detailPath;
        }

        $openapi->paths = $paths;

        $outputPath = storage_path('api-docs/api-docs.json');
        if (!File::exists(dirname($outputPath))) File::makeDirectory(dirname($outputPath), 0777, true);
        
        File::put($outputPath, $openapi->toJson());

        $this->info("SELESAI!");
        $this->info("File: $outputPath");
    }

    /**
     * Helper to create JSON request body (Ensures Object format)
     */
    private function makeJsonRequestBody(array $properties, array $required = []): OA\RequestBody
    {
        return new OA\RequestBody([
            'required' => true,
            'content' => [
                'application/json' => new OA\MediaType([
                    'mediaType' => 'application/json',
                    'schema' => new OA\Schema([
                        'type' => 'object',
                        'properties' => $properties, // Karena $properties punya key string, ini akan jadi {}
                        'required' => !empty($required) ? $required : null
                    ])
                ])
            ]
        ]);
    }

    /**
     * Helper to create common error responses so documentation is accurate
     */
    private function makeCommonResponses($successCode = 200, $successMsg = "OK", $isDetail = false): array
    {
        $res = [
            new OA\Response(['response' => $successCode, 'description' => $successMsg]),
            new OA\Response(['response' => 401, 'description' => 'Unauthenticated']),
            new OA\Response(['response' => 403, 'description' => 'Forbidden']),
            new OA\Response(['response' => 500, 'description' => 'Internal Server Error']),
        ];

        if ($isDetail) {
            $res[] = new OA\Response(['response' => 404, 'description' => 'Data Not Found']);
        }

        return $res;
    }
}