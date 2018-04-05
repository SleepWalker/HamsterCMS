<?php
/**
 * MdViewRenderer renderer for rendering of .md files
 *
 * @author     Sviatoslav Danylenko <Sviatoslav.Danylenko@udf.su>
 * @package    ext.hamster
 * @copyright  Copyright &copy; 2015 Sviatoslav Danylenko (http://hamstercms.com)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
namespace ext\hamster;

class MdViewRenderer extends \CViewRenderer
{
    /**
     * @var string the extension name of the view file. Defaults to '.php'.
     */
    public $fileExtension = '.md';

    /**
     * Parses the source view file and saves the results as another file.
     * @param string $sourceFile the source view file path
     * @param string $viewFile the resulting view file path
     */
    protected function generateViewFile($sourceFile, $viewFile)
    {
        $text = file_get_contents($sourceFile);

        // replacing {{var_name}} -> <?= $var_name ? >
        $text = preg_replace('/\{\{([^\{]+)\}\}/', '<?= \$$1 ?? "" ?>', $text);

        $html = (new \CMarkdownParser())->transform($text);

        file_put_contents($viewFile, $html);
    }
}
