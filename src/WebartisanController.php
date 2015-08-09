<?php

namespace Emir\Webartisan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class WebartisanController extends Controller
{
    /**
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('webartisan::index');
    }

    /**
     * @param Request $request
     *
     * RPC handler
     *
     * @return array
     */
    public function actionRpc(Request $request)
    {
        $options = json_decode($request->getContent());

        switch ($options->method) {
            case 'artisan':
                list($status, $output) = $this->runCommand(implode(' ', $options->params));

                return ['result' => $output];
        }
    }

    /**
     * Runs console command.
     *
     * @param string $command
     *
     * @return array [status, output]
     */
    private function runCommand($command)
    {
        $cmd = base_path("artisan $command 2>&1");

        $handler = popen($cmd, 'r');
        $output = '';
        while (!feof($handler)) {
            $output .= fgets($handler);
        }
        $output = trim($output);
        $status = pclose($handler);

        return [$status, $output];
    }
}
