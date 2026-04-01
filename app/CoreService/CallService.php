<?php

namespace App\CoreService;

use App\CoreService\CoreException;
use App\CoreService\CoreResponse;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CallService
{

    private static function resolveServiceClass($serviceName)
    {
        $services = config('service'); 

        if (is_null($services) || !is_array($services)) {
            throw new \Exception("File konfigurasi 'config/service.php' tidak ditemukan atau kosong.");
        }

        foreach ($services as $service) {
            if (isset($service['class']) && \Illuminate\Support\Str::endsWith($service['class'], $serviceName)) {
                return $service['class'];
            }
        }

        return $serviceName;
    }

    public static function run($serviceName, $input)
    {
        $object = null;
        try {
            $className = self::resolveServiceClass($serviceName);
            $object = app()->make($className);

            if (isset($object->task) && $object->task != null) {
                if (!hasPermission($object->task)) {
                    throw new CoreException("Forbidden", 403);
                }
            }

            $useTransaction = isset($object->transaction) && $object->transaction;

            if ($useTransaction) {
                DB::beginTransaction();
            }

            $input["session"] = [
                "datetime" => date("YmdHis"),
                "user_id" => Auth::id()
            ];

            $result = $object->execute($input);

            if ($useTransaction) {
                DB::commit();
            }

            return CoreResponse::ok($result);

        } catch (CoreException $ex) {
            if ($object && isset($object->transaction) && $object->transaction) {
                DB::rollback();
            }
            return CoreResponse::fail($ex);

        } catch (Exception $ex) {
            if ($object && isset($object->transaction) && $object->transaction) {
                DB::rollback();
            }

            \Illuminate\Support\Facades\Log::error("Service Error [$serviceName]: " . $ex->getMessage());

            return response()->json([
                "success" => false,
                "error_message" => $ex->getMessage()
            ], 500);
        }
    }

    public static function call($serviceName, $input)
    {
        $className = self::resolveServiceClass($serviceName);
        $object = app()->make($className);
        return $object->execute($input);
    }
}