<?php


namespace PieceofScript\Services\Out;


use PieceofScript\Services\Contexts\AbstractContext;
use PieceofScript\Services\Contexts\ContextStack;
use PieceofScript\Services\Contexts\EndpointContext;
use PieceofScript\Services\Contexts\GlobalContext;
use PieceofScript\Services\Contexts\TestcaseContext;
use PieceofScript\Services\Endpoints\Endpoint;
use PieceofScript\Services\Errors\InternalError;
use PieceofScript\Services\Errors\RuntimeError;
use PieceofScript\Services\Utils\Utils;
use Symfony\Component\Console\Output\OutputInterface;

class Out
{
    const INDENT = 4;
    const STYLES = [
        'error' => '',
    ];
    const FORMATTING = true;

    /** @var OutputInterface */
    protected static $output;

    public static function setOutput(OutputInterface $output)
    {
        static::$output = $output;
    }

    public static function printError(InternalError $e, ContextStack $contextStack = null)
    {
        $verbosity = OutputInterface::VERBOSITY_NORMAL;
        static::writeln('<fg=white;bg=red>Error:</> ' . $e->getMessage(), $verbosity);
        if ($e instanceof RuntimeError && null !== $contextStack) {
            static::printContextStack($contextStack);
        }
    }

    public static function printWarning(string $message, ContextStack $contextStack = null)
    {
        $verbosity = OutputInterface::VERBOSITY_VERY_VERBOSE;
        static::writeln('<fg=yellow>Warning:</> ' . $message, $verbosity);
        if (null !== $contextStack) {
            $context = $contextStack->head();
            static::writeln('in context "' . $context->getName() . '", file "' . $context->getFile() . '" at line ' . ($context->getLine() + 1), $verbosity);
        }
    }

    public static function printDebug(string $message)
    {
        $verbosity = OutputInterface::VERBOSITY_DEBUG;
        static::writeln($message, $verbosity);
    }

    public static function printStatistics(string $message, int $indent = 0)
    {
        $verbosity = OutputInterface::VERBOSITY_NORMAL;
        static::startFormatting('<fg=yellow>', $verbosity);
        static::writeln($message, $verbosity, $indent);
        static::endFormatting($verbosity);
    }

    public static function printContextStack(ContextStack $contextStack)
    {
        $verbosity = OutputInterface::VERBOSITY_NORMAL;

        static::writeln('Call stack:', $verbosity);

        /** @var AbstractContext $context */
        $context = $contextStack->head();
        do {
            $text = 'Context "' . $context->getName() . '"';
            if ($context->getFile()) {
                $text = $text . ', file "' . $context->getFile() . '"';
            }
            if ($context->getLine() !== null) {
                 $text = $text . ' at line ' . ($context->getLine() + 1);
            }
            static::writeln($text, $verbosity, 1);
            $context = $context->getParentContext();
        } while (null !== $context);
    }


    public static function printValues(array $values)
    {
        $verbosity = OutputInterface::VERBOSITY_VERBOSE;
        static::startFormatting('<fg=yellow>', $verbosity);
        foreach ($values as $value) {
            static::write($value,  $verbosity);
        }
        static::endFormatting($verbosity);

        static::writeln('', $verbosity);
    }

    public static function printLine(string $line, string $lineNumber)
    {
        $line = trim($line, PHP_EOL);
        $verbosity = OutputInterface::VERBOSITY_DEBUG;
        //$lineNumber = str_pad($lineNumber, 4, ' ', STR_PAD_LEFT);
        static::writeln($lineNumber . ': '. $line, $verbosity);
    }

    public static function printCancel()
    {
        $verbosity = OutputInterface::VERBOSITY_DEBUG;
        static::writeln('Testing is cancelled', $verbosity);
    }

    public static function printAssert(string $code, bool $success, string $message)
    {
        $message = empty($message) ? '' : ': ' . $message;
        if ($success) {
            $verbosity = OutputInterface::VERBOSITY_DEBUG;
            static::startFormatting('<fg=green>', $verbosity);
            static::writeln('Assert: "' . trim($code) . '" successful' . $message, $verbosity);
            static::endFormatting($verbosity);
        } else {
            $verbosity = OutputInterface::VERBOSITY_NORMAL;
            static::startFormatting('<fg=cyan>', $verbosity);
            static::writeln('Assert: "' . trim($code) . '" failed' . $message, $verbosity);
            static::endFormatting($verbosity);
        }
    }

    public static function printMustExit(ContextStack $contextStack)
    {
        $verbosity = OutputInterface::VERBOSITY_DEBUG;
        if ($contextStack->head() instanceof GlobalContext) {
            static::writeln('Testing is terminated', $verbosity);
        } elseif ($contextStack->head() instanceof TestcaseContext) {
            static::writeln('Rest of test case "' . $contextStack->head()->getName() . '" is skipped', $verbosity);
        } elseif ($contextStack->head() instanceof EndpointContext) {
            static::writeln('Rest of test case "' . $contextStack->neck()->getName() . '" is skipped', $verbosity);
        } else {
            static::writeln('Command Must was called in inappropriate context', OutputInterface::VERBOSITY_NORMAL);
        }
    }

    public static function printRequest($request)
    {
        $verbosity = OutputInterface::VERBOSITY_DEBUG;
        static::startFormatting('<fg=blue>', $verbosity);
        $request = Utils::unwrapValueContainer($request);
        $url = self::getPrintableUrl($request);
        static::writeln('Request: ' . $request['method'] . ' ' . $url , $verbosity, 0);

        if (!empty($request['headers'])) {
            static::writeln('Headers:', $verbosity, 1);
            foreach ($request['headers'] as $name => $value) {
                static::writeln($name . ': ' . $value, $verbosity, 2);
            }
        }
        if (!empty($request['cookies'])) {
            static::writeln('Cookies:', $verbosity, 1);
            foreach ($request['cookies'] as $name => $value) {
                static::writeln($name . ': ' . $value, $verbosity, 2);
            }
        }

        if ($request['format'] === Endpoint::FORMAT_JSON) {
            static::writeln('JSON body:', $verbosity, 1);
            static::writeln(json_encode($request['data'], JSON_PRETTY_PRINT), $verbosity, 2);
        } elseif ($request['format'] === Endpoint::FORMAT_FROM) {
            static::writeln('Form data:', $verbosity, 1);
            static::printFormData($request['data'], $verbosity, 2);
        } elseif ($request['format'] === Endpoint::FORMAT_MULTIPART) {
            static::writeln('Multipart form data:', $verbosity, 1);
            static::printMultipartFormData($request['data'], $verbosity, 2);
        } elseif ($request['format'] === Endpoint::FORMAT_NONE) {
            // Do nothing
        } else {
            throw new RuntimeError('Unknown request format "' . $request['format'] . '"');
        }
        static::endFormatting($verbosity);
    }

    protected static function getPrintableUrl($request)
    {
        if (empty($request['url'])) {
            throw new RuntimeError('Error output request data. Url is missed');
        }

        $url = $request['url'];
        if (!empty($request['query'])) {
            if (is_array($request['query'])) {
                $url = explode('?', $url)[0] . '?' . http_build_query($request['query']);
            } else {
                $url = explode('?', $url)[0] . '?' . $request['query'];
            }
        }

        return $url;
    }

    protected static function printFormData($formData, $verbosity, $indent = 0)
    {
        if (is_array($formData)) {
            foreach ($formData as $key => $value) {
                static::write($key, $verbosity, $indent);
                if (is_array($value)) {
                    static::writeln(': [', $verbosity, $indent);
                    static::printFormData($value, $verbosity, $indent + 1);
                    static::writeln(']', $verbosity, $indent);
                } else {
                    static::write(': ', $verbosity, $indent);
                    static::write((string) $value, $verbosity, $indent);
                }
            }
        } else {
            static::writeln((string) $formData, $verbosity, $indent);
        }
    }

    protected static function printMultipartFormData($formData, $verbosity, $indent = 0)
    {
        if (is_array($formData)) {
            foreach ($formData as $key => $value) {
                static::write($key, $verbosity, $indent);
                if (is_array($value)) {

                } else {
                    static::write(': ', $verbosity, $indent);
                    static::write((string) $value, $verbosity, $indent);
                }
            }
        } else {
            static::writeln((string) $formData, $verbosity, $indent);
        }
    }

    public static function printResponse($response)
    {
        $verbosity = OutputInterface::VERBOSITY_DEBUG;
        static::startFormatting('<fg=magenta>', $verbosity);
        $response = Utils::unwrapValueContainer($response);
        if (!$response['network']) {
            static::writeln('Network error', $verbosity);
            return;
        }

        static::writeln('Response: ' . $response['status'], $verbosity);
        if (!empty($response['headers'])) {
            static::writeln('Headers:', $verbosity, 1);
            foreach ($response['headers'] as $name => $value) {
                static::writeln($name . ': ' . $value, $verbosity, 2);
            }
        }
        if (!empty($response['cookies'])) {
            static::writeln('Cookies:', $verbosity, 1);
            foreach ($response['cookies'] as $name => $value) {
                static::writeln($name . ': ' . $value['Value'], $verbosity, 2);
            }
        }
        if (!empty($response['raw'])) {
            static::writeln('Body:', $verbosity, 1);
            if (!empty($response['body'])) {
                static::writeln(json_encode($response['body'], JSON_PRETTY_PRINT), $verbosity, 2);
            } else {
                static::writeln($response['raw'], $verbosity, 1);
            }
        }
        static::endFormatting($verbosity);
        static::writeln('', $verbosity);
    }

    protected static function writeln($text, int $verbosity, int $indent = 0)
    {
        $parts = explode("\n", $text);
        foreach ($parts as $part) {
            static::$output->writeln(str_repeat(' ', $indent * self::INDENT) . $part, $verbosity);
        }
    }

    protected static function write($text, int $verbosity, int $indent = 0)
    {
        static::$output->write(str_repeat(' ', $indent * self::INDENT) . $text, false, $verbosity);
    }

    protected static function startFormatting($formatting, $verbosity)
    {
        if (static::FORMATTING) {
            static::$output->write($formatting, false, $verbosity);
        }
    }

    protected static function endFormatting($verbosity)
    {
        if (static::FORMATTING) {
            static::$output->write('</>', false, $verbosity);
        }
    }
}