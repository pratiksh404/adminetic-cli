<?php

namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;
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
        $name = $this->argument('name');
        $currentPath = getcwd();
        $validPath = str_replace("/", "\\", $currentPath);
        $projectPath = $validPath . "\\" . $name;
        $this->info("Creating new adminetic project: $name ...");
        /* Downloading Laravel */
        $this->task("Installing Laravel", function () use ($name) {
            $process = shell_exec("laravel new $name");
            return !empty($process);
        });
        $this->task("Downloading Adminetic Admin Panel", function () use ($projectPath) {

            $process = shell_exec("cd $projectPath && composer require pratiksh/adminetic");
            return !empty($process);
        });
        $database_created = $this->ask("Have you create database name $name ? (y/n)");
        if ($database_created == 'y' || $database_created == 'Y') {
            $this->task("Migrating Database Schema", function () use ($projectPath) {
                $process = shell_exec("cd $projectPath && php artisan migrate");
                return !empty($process);
            });
            $this->task("Replacing Route File and User Model", function () use ($projectPath) {
                /* Deleting web.php and User.php */
                file_exists($projectPath . '\routes\web.php') ? unlink($projectPath . '\routes\web.php') : '';
                file_exists($projectPath . '\app/Models\User.php') ? unlink($projectPath . '\app\Models\User.php') : '';
                /* Adminetic adminetic web.php and User.php */
                file_put_contents($projectPath . '/routes/web.php', file_get_contents(__DIR__ . '/Stubs/web.stub'));
                file_put_contents($projectPath . '/app/Models/User.php', file_get_contents(__DIR__ . '/Stubs/User.stub'));
            });
            $this->task("Installing Adminetic Admin Panel", function () use ($projectPath) {
                $process = shell_exec("cd $projectPath && php artisan install:adminetic");
                return !empty($process);
            });
            $this->task("Seeding Data", function () use ($projectPath) {
                $process = shell_exec("cd $projectPath && php artisan adminetic:dummy");
                return !empty($process);
            });
        }
        $this->info("Project created successfully ... ✅");
        $this->info("cd $name");
        $this->info("admin Credential");
        $this->info("email: admin@admin.com");
        $this->info("password: admin123");
        $this->info("Create something awesome ... 🎉");
        $this->notify("Adminetic Project Created Successfully", "Create something awesome ... 🎉", __DIR__ . '../../assets/logo.png');
    }

    /**
     * Define the command's schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }
}
