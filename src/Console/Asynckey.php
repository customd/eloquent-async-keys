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


    public function handle(): void
    {
        $this->confirmDirectory();
        $password = config('app.key');

        if (! is_string($password) || empty($password)) {
            throw new \Exception('Application Key required');
        }

        $publicKey = storage_path() . '/_certs/public.key';
        $privateKey = storage_path() . '/_certs/private.key';

        $overwrite = boolval($this->option('overwrite'));

        /** @var array{versions: array<string,string>, default: string} $config */
        $config = config('eloquent-async-keys');
        $rsa = new Keypair($config, $publicKey, $privateKey, $password);

        try {
            $rsa->create(null, $overwrite);
        } catch (\Exception $exeption) {
            $this->error($exeption->getMessage());
        }
    }

    /**
     * confimrm the directory exists.
     */
    protected function confirmDirectory(): void
    {
        if (! is_dir(\storage_path() . '/_certs')) {
            mkdir(\storage_path() . '/_certs', 0770, true);
        }
    }
}
