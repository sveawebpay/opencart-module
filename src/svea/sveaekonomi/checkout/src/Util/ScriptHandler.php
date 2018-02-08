<?php

namespace Svea\Checkout\Util;

class ScriptHandler
{
    const VENDOR_BIN_PATH = '/vendor/bin/';
    const SRC_PATH = '/src/';
    const TESTS_PATH = '/tests/';
    
    /**
     * Create log folder if folder does not exist
     */
    public static function createLogFolder()
    {
        if (!file_exists('build/logs')) {
            mkdir('build/logs', 0777, true);
        }
    }

    /**
     * Measuring the size and analyzing the structure of a PHP project
     */
    public static function codeAnalysisPhpLoc()
    {
        $rootPath = getcwd();
        $logDestinationPath = $rootPath . '/build/logs/phploc.csv';
        $srcPath = $rootPath . self::SRC_PATH;
        $testsPath = $rootPath . self::TESTS_PATH;
        
        self::executeScript('phploc', "--log-csv $logDestinationPath $srcPath $testsPath --quiet");
    }

    /**
     * Copy/Paste Detector (CPD) for PHP code.
     */
    public static function codeAnalysisPhpCpd()
    {
        $rootPath = getcwd();
        $logDestinationPath = $rootPath . '/build/logs/pmd-cpd.xml';
        $srcPath = $rootPath . self::SRC_PATH;

        self::executeScript('phpcpd', "--log-pmd $logDestinationPath $srcPath --quiet");
    }

    /**
     * Detect violations of a defined coding standard for PHP, JavaScript and CSS files
     */
    public static function codeAnalysisPhpCs()
    {
        $rootPath = getcwd();
        $ruleSetFilePath = $rootPath . '/cs-ruleset.xml';
        $logDestinationPath = $rootPath . '/build/logs/checkstyle.xml';
        $srcPath = $rootPath . self::SRC_PATH;
        $testsPath = $rootPath . self::TESTS_PATH;

        $params = "--standard=$ruleSetFilePath --report=checkstyle --report-file=$logDestinationPath --extensions=php ";
        $params .= "$srcPath $testsPath";

        self::executeScript('phpcs', $params);
    }

    /**
     * PHP Mess Detector Analyse a given PHP source code and look for several potential problems within that source.
     * (Possible bugs, Suboptimal code, Overcomplicated expressions, Unused parameters, methods, properties)
     */
    public static function codeAnalysisPhpMd()
    {
        $rootPath = getcwd();
        $ruleSetFilePath = $rootPath . '/phpmd.xml';
        $logDestinationPath = $rootPath . '/build/logs/pmd.xml';
        $srcPath = $rootPath . self::SRC_PATH;
        $testsPath = $rootPath . self::TESTS_PATH;

        $params = "$srcPath,$testsPath xml $ruleSetFilePath --reportfile $logDestinationPath --quiet";
        self::executeScript('phpmd', $params);
    }

    /**
     * PHP CodeSniffer is able to fix many errors and warnings automatically.
     * Code Beautifier and Fixer (phpcbf) can be used in place of phpcs to automatically generate and
     * apply the diff for you.
     */
    public static function fixSniffViolations()
    {
        $rootPath = getcwd();
        $ruleSetFilePath = $rootPath . '/cs-ruleset.xml';
        $srcPath = $rootPath . self::SRC_PATH;
        $testsPath = $rootPath . self::TESTS_PATH;

        self::executeScript('phpcbf', "--standard=$ruleSetFilePath $srcPath $testsPath --no-patch --quiet");
    }

    /**
     * Run All unit tests (phpUnit)
     */
    public static function runUnitTests()
    {
        $rootPath = getcwd();
        $configurationFilePath = $rootPath . '/phpunit.xml.dist';

        self::executeScript('phpunit', "--configuration $configurationFilePath");
    }

    /**
     * Create documentation with phpDocumentor
     */
    public static function createPhpDocumentation()
    {
        $rootPath = getcwd();
        $srcPath = $rootPath . self::SRC_PATH;
        $documentationFolderPath = $rootPath . '/docs/api';
        $cachePath = $documentationFolderPath . '/cache';

        self::executeScript('phpdoc', "-d $srcPath -t $documentationFolderPath --cache-folder  $cachePath");
    }

    private static function executeScript($command, $params)
    {
        $rootPath = getcwd();
        $command = $rootPath . self::VENDOR_BIN_PATH . "$command  $params";
        
        exec($command);
    }
}
