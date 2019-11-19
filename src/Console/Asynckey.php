<?php

namespace CustomD\EloquentAsyncKeys\Console;

use Illuminate\Console\Command;
use CustomD\EloquentAsyncKeys\Keypair;

class Asynckey extends Command
{
    /**
     * the console command name.
     *
     * @var string
     */
    protected $name = 'asynckey';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates filebased public private keys for your application';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'asynckey {--overwrite}';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->confirmDirectory();
        $password = config('app.key');

        if (! $password) {
            throw new \Exception('Application Key required');
        }

        $publicKey = storage_path() . '/_certs/public.key';
        $privateKey = storage_path() . '/_certs/private.key';

        $overwrite = $this->option('overwrite');

        $rsa = new Keypair(config('eloquent-async-keys'), $publicKey, $privateKey, $password);

        try {
            $rsa->create(null, $overwrite);
        } catch (\Exception $exeption) {
            $this->error($exeption->getMessage());
        }
    }

    /**
     * confimrm the directory exists.
     */
    protected function confirmDirectory()
    {
        if (! is_dir(\storage_path() . '/_certs')) {
            mkdir(\storage_path() . '/_certs', 0770, true);
        }
    }
}
