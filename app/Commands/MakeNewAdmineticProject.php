<?php

namespace App\Commands;

use LaravelZero\Framework\Commands\Command;

class MakeNewAdmineticProject extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'new {name : The name of the project (required)}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Command to create a new adminetic project.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->line("
           ___     ___          _            _   _                 _ _
          / _ \    | |         (_)          | | (_)               | (_)
         / /_\ \ __| |_ __ ___  _ _ __   ___| |_ _  ___ ______ ___| |_ 
         |  _  |/ _` | '_ ` _ \| | '_ \ / _ \ __| |/ __|______/ __| | |
         | | | | (_| | | | | | | | | | |  __/ |_| | (__      | (__| | |
         \_| |_/\__,_|_| |_| |_|_|_| |_|\___|\__|_|\___|      \___|_|_|                                                           
        ");
        $name = $this->argument('name');
        $currentPath = getcwd();
        $validPath = str_replace('/', '\\', $currentPath);
        $projectPath = $validPath.'\\'.$name;
        $this->info("Creating new adminetic project: $name ...");
        /* Downloading Laravel */
        $this->task('Installing Laravel', function () use ($name) {
            $process = shell_exec("composer create-project laravel/laravel:^10.* $name");

            return !empty($process);
        });
        $this->task('Downloading Adminetic Admin Panel', function () use ($projectPath) {
            $process = shell_exec("cd $projectPath && composer require pratiksh/adminetic");

            return !empty($process);
        });
        $this->task('Install Adminetic Admin Panel', function () use ($name, $projectPath) {
            $this->afterDBConffirmationProcess($this, $name, $projectPath);
        });
        $this->info('Project created successfully ... ✅');
        $this->info("cd $name");
        $this->info('admin Credential');
        $this->info('email: admin@admin.com');
        $this->info('password: admin123');
        $this->info('Create something awesome ... 🎉');
        $this->notify('Adminetic Project Created Successfully', 'Create something awesome ... 🎉', __DIR__.'../../assets/logo.png');
    }

    protected function afterDBConffirmationProcess(Command $command, $name, $projectPath)
    {
        $database_created = $command->ask("Have you create database named $name ? (y/n)");
        if ($database_created == 'y' || $database_created == 'Y') {
            $command->task('Migrating Database Schema', function () use ($projectPath) {
                $process = shell_exec("cd $projectPath && php artisan migrate");

                return !empty($process);
            });
            $command->task('Replacing Route File and User Model', function () use ($projectPath) {
                /* Deleting web.php and User.php */
                file_exists($projectPath.'\routes\web.php') ? unlink($projectPath.'\routes\web.php') : '';
                file_exists($projectPath.'\app/Models\User.php') ? unlink($projectPath.'\app\Models\User.php') : '';
                /* Adminetic adminetic web.php and User.php */
                file_put_contents($projectPath.'/routes/web.php', file_get_contents(__DIR__.'/Stubs/web.stub'));
                file_put_contents($projectPath.'/app/Models/User.php', file_get_contents(__DIR__.'/Stubs/User.stub'));
            });

            shell_exec("cd $projectPath && php artisan install:adminetic");
            $command->task('Seeding Data', function () use ($projectPath) {
                $process = shell_exec("cd $projectPath && php artisan adminetic:dummy");

                return !empty($process);
            });
        } else {
            $command->info("Please create database named $name");
            $this->afterDBConffirmationProcess($command, $name, $projectPath);
        }
    }
}
