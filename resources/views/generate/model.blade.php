namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Hash;

class {{ $studly_caps }} extends Model
{
    use HasFactory;

    protected $table = '{{ $table_name }}';

    protected $fillable = {!! var_export($fieldAdd, true) !!};

    public const FIELD_LIST = {!! var_export($fieldList, true) !!};
    public const FIELD_ADD = {!! var_export($fieldAdd, true) !!};
    public const FIELD_EDIT = {!! var_export($fieldEdit, true) !!};
    public const FIELD_VALIDATION = {!! var_export($fieldValidation, true) !!};
    public const FIELD_UNIQUE = {!! var_export($fieldUnique, true) !!};
    public const FIELD_UPLOAD = {!! var_export($fieldUpload, true) !!};
    public const FILEROOT = '{{ $fileRoot }}';

    public const FIELD_RELATION = [
@foreach($fieldRelation as $key => $rel)
        '{{ $key }}' => [
            'linkTable' => '{{ $rel['table'] }}',
            'linkField' => '{{ $rel['field'] }}',
            'selectField' => '{{ $rel['selectField'] }}',
            'displayName' => '{{ $rel['alias'] }}',
        ],
@endforeach
    ];

    public static function beforeInsert($input)
    {
@if($has_password)
        if (isset($input['password']) && !empty($input['password'])) {
            $input['password'] = Hash::make($input['password']);
        }
        if (isset($input['pin']) && !empty($input['pin'])) {
            $input['pin'] = Hash::make($input['pin']);
        }
@endif
        return $input;
    }

    public static function beforeUpdate($input)
    {
@if($has_password)
        if (isset($input['password']) && !empty($input['password'])) {
            $input['password'] = Hash::make($input['password']);
        } else {
            unset($input['password']);
        }

        if (isset($input['pin']) && !empty($input['pin'])) {
            $input['pin'] = Hash::make($input['pin']);
        } else {
            unset($input['pin']);
        }
@endif
        return $input;
    }

    public static function beforeInsert($input)
    {
        return $input;
    }

    public static function afterInsert($object, $input)
    {
        return $input;
    }
    
    public static function beforeUpdate($input)
    {
        return $input;
    }
    
    public static function afterUpdate($object, $input)
    {
        return $input;
    }
    
    public static function beforeDelete($input)
    {
        return $input;
    }

    public static function afterDelete($object, $input)
    {
        return $input;
    }

    // -- Start Custom Code --
    
    // -- End Custom Code --
}