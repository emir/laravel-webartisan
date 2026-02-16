/**
 * Webartisan - Laravel Terminal
 * Browser-based artisan command runner
 */
jQuery(function ($) {
    'use strict';

    var config = window.WebartisanConfig || {};
    var commandList = [];
    var commandMap = {};

    // =========================================================================
    // API Layer
    // =========================================================================

    function api(method, url, data) {
        return $.ajax({
            url: url,
            method: method,
            headers: {
                'X-CSRF-TOKEN': config.csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            data: data ? JSON.stringify(data) : undefined,
            dataType: 'json'
        });
    }

    // =========================================================================
    // Command Registry
    // =========================================================================

    function fetchCommands() {
        api('GET', config.commandsUrl).done(function (data) {
            var commands = data.commands || [];
            commandList = [];
            commandMap = {};

            commands.forEach(function (cmd) {
                var name = typeof cmd === 'string' ? cmd : cmd.name;
                var desc = typeof cmd === 'string' ? '' : (cmd.description || '');
                commandList.push(name);
                commandMap[name] = desc;
            });
        });
    }

    // =========================================================================
    // Command Execution
    // =========================================================================

    function runCommand(command, term) {
        term.pause();

        api('POST', config.runUrl, { command: command })
            .done(function (data) {
                if (data.output) {
                    if (data.success) {
                        term.echo(data.output);
                    } else {
                        term.echo('[[;#f38ba8;]' + escapeFormatting(data.output) + ']');
                    }
                }
            })
            .fail(function (xhr) {
                var response = xhr.responseJSON;
                if (response && response.output) {
                    term.error(response.output);
                } else if (xhr.status === 419) {
                    term.error('CSRF token mismatch. Please refresh the page.');
                } else if (xhr.status === 403) {
                    term.error('Access denied.');
                } else {
                    term.error('An unexpected error occurred (HTTP ' + xhr.status + ').');
                }
            })
            .always(function () {
                term.resume();
            });
    }

    // =========================================================================
    // Helpers
    // =========================================================================

    function escapeFormatting(text) {
        return text.replace(/\[/g, '&#91;').replace(/\]/g, '&#93;');
    }

    function padRight(str, len) {
        while (str.length < len) {
            str += ' ';
        }
        return str;
    }

    // =========================================================================
    // Built-in Commands
    // =========================================================================

    var builtinCommands = {
        help: function (term) {
            term.echo('');
            term.echo('[[b;#89b4fa;]BUILT-IN COMMANDS]');
            term.echo('  [[b;#cdd6f4;]help]           Show this help message');
            term.echo('  [[b;#cdd6f4;]list]           List available artisan commands');
            term.echo('  [[b;#cdd6f4;]clear]          Clear the terminal screen');
            term.echo('  [[b;#cdd6f4;]exit] / [[b;#cdd6f4;]quit]    Leave webartisan');
            term.echo('');
            term.echo('[[b;#89b4fa;]USAGE]');
            term.echo('  Type any artisan command directly:');
            term.echo('  [[;#a6e3a1;]route:list]');
            term.echo('  [[;#a6e3a1;]migrate:status]');
            term.echo('  [[;#a6e3a1;]make:model User --migration]');
            term.echo('');
            term.echo('[[b;#89b4fa;]TIPS]');
            term.echo('  [[;#fab387;]Tab]             Autocomplete command names');
            term.echo('  [[;#fab387;]Up/Down]         Navigate command history');
            term.echo('  [[;#fab387;]Ctrl+L]          Clear terminal');
            term.echo('');
        },

        list: function (term) {
            if (commandList.length === 0) {
                runCommand('list', term);
                return;
            }

            var maxLen = 0;
            commandList.forEach(function (name) {
                if (name.length > maxLen) maxLen = name.length;
            });

            var currentGroup = '';

            term.echo('');
            term.echo('[[b;#89b4fa;]AVAILABLE COMMANDS]');
            term.echo('');

            commandList.forEach(function (name) {
                var parts = name.split(':');
                var group = parts.length > 1 ? parts[0] : '';
                var desc = commandMap[name] || '';

                if (group !== currentGroup) {
                    if (group) {
                        term.echo(' [[b;#fab387;]' + group + ']');
                    }
                    currentGroup = group;
                }

                term.echo('  [[;#a6e3a1;]' + padRight(name, maxLen + 4) + '][[;#6c7086;]' + desc + ']');
            });
            term.echo('');
        },

        quit: function (term) {
            term.echo('[[;#a6e3a1;]Goodbye!]');
            if (config.exitUrl) {
                setTimeout(function () {
                    location.replace(config.exitUrl);
                }, 600);
            }
        },

        exit: function (term) {
            builtinCommands.quit(term);
        }
    };

    // =========================================================================
    // Greeting Banner
    // =========================================================================

    function getGreeting() {
        var lines = [
            '',
            '  [[b;#a6e3a1;]██╗    ██╗███████╗██████╗  █████╗ ██████╗ ████████╗██╗███████╗ █████╗ ███╗   ██╗]',
            '  [[b;#a6e3a1;]██║    ██║██╔════╝██╔══██╗██╔══██╗██╔══██╗╚══██╔══╝██║██╔════╝██╔══██╗████╗  ██║]',
            '  [[b;#a6e3a1;]██║ █╗ ██║█████╗  ██████╔╝███████║██████╔╝   ██║   ██║███████╗███████║██╔██╗ ██║]',
            '  [[b;#a6e3a1;]██║███╗██║██╔══╝  ██╔══██╗██╔══██║██╔══██╗   ██║   ██║╚════██║██╔══██║██║╚██╗██║]',
            '  [[b;#a6e3a1;]╚███╔███╔╝███████╗██████╔╝██║  ██║██║  ██║   ██║   ██║███████║██║  ██║██║ ╚████║]',
            '  [[b;#a6e3a1;] ╚══╝╚══╝ ╚══════╝╚═════╝ ╚═╝  ╚═╝╚═╝  ╚═╝   ╚═╝   ╚═╝╚══════╝╚═╝  ╚═╝╚═╝  ╚═══╝]',
            '',
            '  [[;#6c7086;]Laravel Terminal v' + config.version + ' — ' + config.environment + ' environment]',
            '  [[;#6c7086;]Type] [[b;#cdd6f4;]help] [[;#6c7086;]for available commands,] [[b;#cdd6f4;]list] [[;#6c7086;]for artisan commands.]',
            ''
        ];

        return lines.join('\n');
    }

    // =========================================================================
    // Terminal Initialization
    // =========================================================================

    fetchCommands();

    var term = $('#webartisan').terminal(
        function (input, term) {
            input = $.trim(input);

            if (!input) {
                return;
            }

            // Strip optional "php artisan" or "artisan" prefix
            var command = input.replace(/^(php\s+)?artisan\s+/, '');

            // Check builtin commands first (use original input)
            var baseCmd = command.split(/\s+/)[0];

            if (builtinCommands[input]) {
                builtinCommands[input](term);
            } else if (builtinCommands[baseCmd] && !commandList.includes(baseCmd)) {
                builtinCommands[baseCmd](term);
            } else {
                runCommand(command, term);
            }
        },
        {
            greetings: getGreeting(),
            name: 'webartisan',
            prompt: '[[b;#a6e3a1;]❯] ',
            completion: function (input, callback) {
                callback(commandList);
            },
            checkArity: false,
            outputLimit: 1000,
            height: '100%',
            clear: true,
            historySize: 200,
            mobileDelete: true,
            convertLinks: false,
            onClear: function () {
                term.echo(getGreeting());
            }
        }
    );

    // Focus terminal on any keypress
    $(document).on('keydown', function (e) {
        if (!$(e.target).is('input, textarea, select')) {
            term.focus();
        }
    });
});
