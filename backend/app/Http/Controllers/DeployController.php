<?php

namespace App\Http\Controllers;

use App\Services\DeployRunner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Manual "deploy now" trigger (the automated path is the `deploy:check` cron job). */
class DeployController extends Controller
{
    public function __invoke(Request $request, DeployRunner $runner): JsonResponse
    {
        $expected = (string) config('services.deploy.token');
        $given = (string) $request->header('X-Deploy-Token');

        if ($expected === '' || ! hash_equals($expected, $given)) {
            return response()->json(['ok' => false, 'error' => 'Forbidden'], 403);
        }

        $results = $runner->run();
        $ok = $results['backend']['ok'] && $results['frontend']['ok'];

        return response()->json(['ok' => $ok, 'results' => $results], $ok ? 200 : 500);
    }
}
