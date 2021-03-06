<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Magento
 * @package     Magento
 * @subpackage  static_tests
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * PHP Copy/Paste Detector shell command
 */
class Inspection_CopyPasteDetector_Command extends Inspection_CommandAbstract
{
    /**
     * @var int|null
     */
    protected $_minLines;

    /**
     * @var int|null
     */
    protected $_minTokens;

    /**
     * Constructor
     *
     * @param string $reportFile Destination file to write inspection report to
     * @param int|null $minLines Minimum number of identical lines
     * @param int|null $minTokens Minimum number of identical tokens
     */
    public function __construct($reportFile, $minLines = null, $minTokens = null)
    {
        parent::__construct($reportFile);
        $this->_minLines = $minLines;
        $this->_minTokens = $minTokens;
    }

    /**
     * @return string
     */
    public function _buildVersionShellCmd()
    {
        return 'phpcpd --version';
    }

    /**
     * @param array $whiteList
     * @param array $blackList
     * @return string
     */
    protected function _buildShellCmd($whiteList, $blackList)
    {
        $whiteList = array_map('escapeshellarg', $whiteList);
        $whiteList = implode(' ', $whiteList);

        if ($blackList) {
            $blackList = array_map('escapeshellarg', $blackList);
            $blackList = '--exclude ' . implode(' --exclude ', $blackList);
        } else {
            $blackList = '';
        }

        return 'phpcpd'
            . ' --log-pmd ' . escapeshellarg($this->_reportFile)
            . ($blackList ? ' ' . $blackList : '')
            . ($this->_minLines ? ' --min-lines ' . $this->_minLines : '')
            . ($this->_minTokens ? ' --min-tokens ' . $this->_minTokens : '')
            . ' ' . $whiteList
        ;
    }

    /**
     * Runs command and produces report in html format
     *
     * @param array $whiteList
     * @param array $blackList
     * @return bool
     */
    public function run(array $whiteList, array $blackList = array())
    {
        $result = parent::run($whiteList, $blackList);
        if ($result) {
            $generateHtmlResult = $this->_generateHtmlReport();
            if ($generateHtmlResult === false) {
                $result = false;
            }
        }
        return $result;
    }

    /**
     * Under Unix platform creates one more report in html-format.
     * The report is generated based on already existing report in xml format and XSLT conversion scheme
     * in "html_report.xslt". Returns null, if conversion tool was not found, otherwise returns true/false as a result
     * of conversion tool execution.
     *
     * @return bool|null
     */
    protected function _generateHtmlReport()
    {
        $isWindows = strncasecmp(PHP_OS, 'win', 3) == 0;
        if ($isWindows) {
            return null;
        }
        if (!$this->_execShellCmd('xsltproc --version')) {
            return null;
        }
        $xsltFile = __DIR__ . '/html_report.xslt';
        $result = $this->_execShellCmd("xsltproc {$xsltFile} {$this->_reportFile} > {$this->_reportFile}.html");
        return $result;
    }
}
