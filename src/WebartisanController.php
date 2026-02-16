<?php

namespace Emir\Webartisan;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\View\View;

class WebartisanController extends Controller
{
    /**
     * Display the webartisan terminal.
     */
    public function index(): View
    {
        $theme = config('webartisan.theme', 'dark');

        return view('webartisan::index', [
            'theme' => $theme,
            'version' => Webartisan::VERSION,
        ]);
    }

    /**
     * Run an artisan command and return the output.
     */
    public function run(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'command' => ['required', 'string', 'max:1000'],
        ]);

        $fullCommand = trim($validated['command']);
        $commandName = $this->extractCommandName($fullCommand);

        if (! Webartisan::isCommandAllowed($commandName)) {
            return response()->json([
                'success' => false,
                'output' => "Command '{$commandName}' is not allowed.",
                'exit_code' => 1,
            ], 403);
        }

        try {
            $exitCode = Artisan::call($fullCommand);
            $output = trim(Artisan::output());

            return response()->json([
                'success' => $exitCode === 0,
                'output' => $output,
                'exit_code' => $exitCode,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'output' => $e->getMessage(),
                'exit_code' => 1,
            ], 422);
        }
    }

    /**
     * List all available artisan commands with descriptions.
     */
    public function commands(): JsonResponse
    {
        $commands = collect(Artisan::all())
            ->filter(fn ($command, string $name) => Webartisan::isCommandAllowed($name))
            ->map(fn ($command, string $name) => [
                'name' => $name,
                'description' => $command->getDescription(),
            ])
            ->sortBy('name')
            ->values()
            ->all();

        return response()->json([
            'commands' => $commands,
        ]);
    }

    /**
     * Extract the base command name from a full command string.
     */
    protected function extractCommandName(string $command): string
    {
        $parts = preg_split('/\s+/', $command, 2);

        return $parts[0] ?? '';
    }
}
