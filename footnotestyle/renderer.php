<?php
/**
 * Footnotetypo - Modify the typography of footenotes
 * @author Viktor Söderqvist <viktor@zuiderkwast.se>
 */

 // must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

// we inherit from the XHTML renderer instead directly of the base renderer
require_once DOKU_INC.'inc/parser/xhtml.php';

/**
 * Footnotetypo renderer
 */
class renderer_plugin_footnotestyle extends Doku_Renderer_xhtml {

    /**
     * the format we produce
     */
    function getFormat(){
        return 'xhtml';
    }

    /**
     * This makes it possible to use as the default xhtml renderer
     */
    function canRender($format) {
      return ($format=='xhtml');
    }

    /**
     * Callback for footnote end syntax
     *
     * All rendered content is moved to the $footnotes array and the old
     * content is restored from $store again
     *
     * @author Andreas Gohr
     * @author Viktor Söderqvist
     */
    function footnote_close() {

        // recover footnote into the stack and restore old content
        $footnote = $this->doc;
        $this->doc = $this->store;
        $this->store = '';
        $amal = $this->getConf('amalgamate'); 
        if ($amal != 'off') {
          // check to see if this footnote has been seen before
          $i = array_search($footnote, $this->footnotes);

          if ($i === false) {
              // its a new footnote, add it to the $footnotes array
              $id = count($this->footnotes)+1;
              $this->footnotes[count($this->footnotes)] = $footnote;
          } else {
              // seen this one before, translate the index to an id and save a placeholder
              $i++;
              $id = count($this->footnotes)+1;
              $this->footnotes[count($this->footnotes)] = "@@FNT".($i);
          }
        } else {
          // don't use amalgamated footnotes.
          // every note is considered a new footnote. add it to the $footnotes array
          $id = count($this->footnotes)+1;
          $this->footnotes[count($this->footnotes)] = $footnote;
        }

        // output the footnote reference and link
        $this->doc .= $this->_format_footnote_link($id);
    }

    /**
     * Some modification to the document_end regarding the footnotes
     */
    function document_end() {
        if ( count ($this->footnotes) > 0 ) {
            $this->doc .= '<div class="footnotes">'.DOKU_LF;

            $id = 0;
            foreach ( $this->footnotes as $footnote ) {
                $id++;   // the number of the current footnote

                // check its not a placeholder that indicates actual footnote text is elsewhere
                if (substr($footnote, 0, 5) != "@@FNT") {

                    // open the footnote and set the anchor and backlink
                    $this->doc .= '<div class="fn">';
                    $this->doc .= $this->_format_footnote_bottomlink($id);

                    $amal = $this->getConf('amalgamate');
                    if ('off' != $amal) {
                        // get any other footnotes that use the same markup
                        $alt = array_keys($this->footnotes, "@@FNT$id");

                        if (count($alt)) {
                            foreach ($alt as $ref) {
                                // set anchor and backlink for the other footnotes
                                if ($amal=='comma')
                                    $this->doc .= ', ';
                                elseif ($amal=='spacecomma')
                                    $this->doc .= ' '.DOKU_LF.', '; // dw original
                                else
                                    $this->doc .= DOKU_LF;
                                $this->doc .= $this->_format_footnote_bottomlink($ref+1);
                            }
                        }
                    }

                    if ($this->getConf('stylebottom') == 'dot')
                        $this->doc .= '.';

                    $this->doc .= DOKU_LF;

                    // add footnote markup and close this footnote
                    $this->doc .= $footnote;
                    $this->doc .= '</div>' . DOKU_LF;
                }
            }
            $this->doc .= '</div>'.DOKU_LF;
        }

        // Prepare the TOC
        global $conf;
        if($this->info['toc'] && is_array($this->toc) && $conf['tocminheads'] && count($this->toc) >= $conf['tocminheads']){
            global $TOC;
            $TOC = $this->toc;
        }

        // make sure there are no empty paragraphs
        $this->doc = preg_replace('#<p>\s*</p>#','',$this->doc);
    }


    function _format_footnote_link($id) {
        $style = $this->getConf('style');

        // the number of the footnote
        $link = $id;

        // formatting around the number
        if ($style=='square' || $style=='supersquare')
            $link = '['.$link.']';
        elseif ($style=='rightparen')
            $link .= ')';

        // the link itself
        $link = '<a href="#fn__'.$id.'" name="fnt__'.$id.'" id="fnt__'.$id.'" class="fn_top">'.$link.'</a>';

        // superscript around the link
        if ($style=='super' || $style=='rightparen' || $style=='supersquare')
            $link = '<sup>'.$link.'</sup>';

        return $link;
    }
    
    function _format_footnote_bottomlink($id) {
        $style = $this->getConf('stylebottom');

        $link = $id;

        if ($style=='superrightparen')
          $link .= ')';
        elseif ($style=='square' || $style=='supersquare')
          $link = '['.$link.']';

        $link = '<a href="#fnt__'.$id.'" id="fn__'.$id.'" name="fn__'.$id.'" class="fn_bot">'.$link.'</a>';

        if ($style=='super' || $style=='superrightparen' || $style=='supersquare'){
          $link = '<sup>'.$link.'</sup>';
        }else{
          $link = '<span class="fn_bot_wrapper">'.$link.'</span>';
        }
        return $link;
    }
}

