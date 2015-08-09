jQuery(function($) {
    $('#webartisan').terminal(
        function(command, term) {
            if (command.indexOf('artisan') === 0 || command.indexOf('artisan') === 7) {
                $.jrpc(WebArtisanEndpoint, 'artisan', [command.replace(/^artisan ?/, '')], function(json) {
                    term.echo(json.result);
                });
            } else if (command === 'help') {
                term.echo('Available commands are:');
                term.echo('');
                term.echo("clear\tClear console");
                term.echo('help\tThis help text');
                term.echo('artisan\tartisan command');
                term.echo('quit\tQuit web artisan');
            } else if (command === 'quit') {
                if (exitUrl) {
                    term.echo('Bye!');
                    location.replace(exitUrl);
                } else {
                    term.echo('There is no exit.');
                }
            } else {
                term.echo('Unknown command.');
            }
        },
        {
            greetings: greetings,
            name: 'laravel-webartisan',
            prompt: '$ '
        }
    );
    $('html').on('keydown', function(){
        $('#webartisan').click();
    });
});