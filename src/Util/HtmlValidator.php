<?php

namespace Framework\Util;

use DOMDocument;
use DOMNode;

/**
 * Klass som validerar HTML-data för att se om den är giltigt enligt förbestämda taggar.
 */
class HtmlValidator
{

    /**
     * @var array Array med HTML-taggar som är tillåtna.
     */
    private array $allowedTags;
    /**
     * @var bool Om validatorn ska ge fel då attributer hittats i HTML-taggar.
     */
    private bool $failOnAttribtues;

    public function __construct(array $allowedTags, bool $failOnAttributes){
        $this->allowedTags = $allowedTags;
        $this->failOnAttribtues = $failOnAttributes;
    }

    /**
     * Validerar en HTML-sträng för otillåtna egenskaper.
     * @param string $html HTML-sträng att validera.
     * @return bool Om datan är giltig.
     */
    public function validate(string $html): bool {
        $doc = new DOMDocument();
        $doc->loadHTML($html);

        if(count($doc->childNodes) < 2)
            return true;

        $mainNode = $doc->childNodes[1]->childNodes[0]; // Datan innanför "html" -> "body" som är default för varje DOMDocument

        foreach($mainNode->childNodes as $child){ 
            if(!$this->validateNode($child)){
                return false;
            }
        }

        return true;
    }

    /**
     * Validerar en enskild HTML-nod.
     * @param DOMNode $node Nod att validera.
     * @return bool Om noden är giltig.
     */
    public function validateNode(DOMNode $node): bool {
        if(!in_array($node->nodeName, $this->allowedTags)) {
            return false;
        }

        if($this->failOnAttribtues && $node->hasAttributes()){
            return false;
        }

        foreach($node->childNodes as $child){
            if(!$this->validateNode($child)){
                return false;
            }
        }

        return true;
    }

}