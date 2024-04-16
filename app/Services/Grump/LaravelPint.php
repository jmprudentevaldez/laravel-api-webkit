<?php

namespace App\Services\Grump;

use App\Console\Commands\CodeFormatter;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\AbstractExternalTask;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Custom Grump PHP task
 *
 * @see https://github.com/phpro/grumphp/blob/master/doc/tasks.md
 */
class LaravelPint extends AbstractExternalTask
{
    public static function getConfigurableOptions(): OptionsResolver
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'ide_helper' => false,
        ]);

        $resolver->addAllowedTypes('ide_helper', ['null', 'boolean']);

        return $resolver;
    }

    public function canRunInContext(ContextInterface $context): bool
    {
        return $context instanceof GitPreCommitContext || $context instanceof RunContext;
    }

    public function run(ContextInterface $context): TaskResultInterface
    {
        /** @see \App\Console\Commands\CodeFormatter */
        $config = $this->getConfig()->getOptions();
        $command = 'php artisan app:styler -i -a';

        if (! $config['ide_helper']) {
            $command = 'php artisan app:styler';
        }

        exec($command, $output, $exitCode);

        foreach ($output as $message) {
            echo $message.PHP_EOL;
        }

        if ($exitCode !== Command::SUCCESS) {
            $styleFixerClass = CodeFormatter::class;
            $errorMessage =
                "A command threw an exception (code: $exitCode)  in $styleFixerClass. Please see the logs above";

            return TaskResult::createFailed($this, $context, $errorMessage);
        }

        return TaskResult::createPassed($this, $context);
    }
}
