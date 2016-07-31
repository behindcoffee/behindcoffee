<?php

namespace Helper;

use \cebe\markdown\Parser;
use \cebe\markdown\block\CodeTrait;
use \cebe\markdown\block\HeadlineTrait;
use \cebe\markdown\block\HtmlTrait;
use \cebe\markdown\block\ListTrait;
use \cebe\markdown\block\QuoteTrait;
use \cebe\markdown\block\RuleTrait;
use \cebe\markdown\inline\CodeTrait as BlockCodeTrait;
use \cebe\markdown\inline\EmphStrongTrait as BlockEmphStrongTrait;
use \cebe\markdown\inline\LinkTrait as BlockLinkTrait;

class Cappuchino extends Parser {

    // include block element parsing using traits
    use CodeTrait;
    // use HeadlineTrait;
    // use HtmlTrait {
    //     parseInlineHtml as private;
    // }
    use ListTrait {
        // Check Ul List before headline
        identifyUl as protected identifyBUl;
        consumeUl as protected consumeBUl;
    }
    use QuoteTrait;
    // use RuleTrait {
    //     // Check Hr before checking lists
    //     identifyHr as protected identifyAHr;
    //     consumeHr as protected consumeAHr;
    // }
    // include inline element parsing using traits
    use BlockCodeTrait;
    use BlockEmphStrongTrait;
    use BlockLinkTrait;

    /**
     * @var boolean whether to format markup according to HTML5 spec.
     * Defaults to `false` which means that markup is formatted as HTML4.
     */
    public $html5 = false;

    protected function prepare()
    {
        // reset references
        $this->references = [];
    }

}
