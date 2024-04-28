<?php

namespace Awz\RichContent\Converter;

use Exception;
use DOMNode;
use DOMElement;
use DOMNodeList;
use DOMDocument;

class Tool
{
    protected static array $simpleNodeList = [
        'body'       => 'document',
        'p'          => 'paragraph',
        'div'        => 'paragraph',
        'br'         => 'paragraph',
        'section'    => 'paragraph',
        'footer'     => 'paragraph',
        'nav'        => 'paragraph',
        'header'     => 'paragraph',
        'aside'      => 'paragraph',
        'ul'         => 'unordered-list',
        'ol'         => 'list-item',
        'li'         => 'list-item',
        'h1'         => 'heading-1',
        'h2'         => 'heading-2',
        'h3'         => 'heading-3',
        'h4'         => 'heading-4',
        'h5'         => 'heading-5',
        'h6'         => 'heading-6',
        'blockquote' => 'blockquote',
        'hr'         => 'hr',
        //'img'        => 'image',
    ];

    protected static array $textNodeList = [
        'strong' => 'bold',
        'b'      => 'bold',
        'u'      => 'underline',
        'em'     => 'italic',
        'i'      => 'italic',
        'code'   => 'code',
        '#text'  => null,
        'span'   => null,
    ];

    /**
     * @param DOMNodeList $nodes
     * @param int         $max
     * @return array
     * @throws Exception
     */
    private static function getChildElements(DOMNodeList $nodes, $max = 10000): array {
        if ($max < 0) {
            throw new Exception('Recursive limit reached!!!');
        }

        $content = [];
        /** @var DOMNode[]|DOMElement[] $nodes */
        foreach ($nodes as $node) {
            if ($simple = (self::$simpleNodeList[$node->nodeName] ?? null)) {
                $nodeParse = new Models\Node($simple, new Models\Unit(), self::getChildElements($node->childNodes, --$max));
            } elseif ($node->nodeName === 'a') {
                $nodeParse = new Models\Node("hyperlink", new Models\Link($node->getAttribute('href')), self::getChildElements($node->childNodes, --$max));
            }elseif ($node->nodeName === 'img') {
                $nodeParse = new Models\Node("image", new Models\Image($node->getAttribute('src')), self::getChildElements($node->childNodes, --$max));
            } elseif (array_key_exists($node->nodeName, self::$textNodeList)) {
                $nodeParse = new Models\TextNode("text", Encoding::convertToUtf($node->nodeValue), new Models\Unit(), self::getMarks($node));
            } else {
                $nodeParse = new Models\Node("unknown", new Models\Unit(), []);
            }
            $content[] = $nodeParse->toArray();
        }

        return $content;
    }

    /**
     * @param DOMNode $node
     * @return array
     */
    private static function getMarks(DOMNode $node): array {
        $marks = [];
        if ($mapping = (self::$textNodeList[$node->nodeName] ?? null)) {
            $marks[] = new Models\TextMark($mapping);
        }
        return $marks;
    }

    /**
     * @param string $html
     * @return array
     * @throws Exception
     */
    public static function parse(string $html): array {
        libxml_use_internal_errors(true);

        $dom = new DOMDocument('1.0');
        //$dom->loadHTML(trim(preg_replace(['/[\n\r\t]/', '/[[:space:]]+/u'], ['', ' '], $html)));
        $dom->loadHTML(Encoding::convertEntities($html));

        $xpathDom = new \DOMXPath($dom);
        $parse    = self::getChildElements($xpathDom->query('//html/*'));
        return !empty($parse) ? current($parse) : [];
    }

    public static function DOMinnerHTML($element)
    {
        $innerHTML = "";

        if(!empty($element['content'])){
            foreach ($element['content'] as $child)
            {
                $innerHTML .= self::DOMinnerHTML($child);
            }
        }elseif(isset($element['value'])){
            $innerHTML .= trim($element['value']);
        }

        return $innerHTML;
    }
}
