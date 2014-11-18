<?php
/**
 * A FeedItem is a part of a FeedCreator feed.
 *
 * @author Kai Blankenhorn <kaib@bitfolge.de>
 * @since 1.3
 */
class FeedItem extends HtmlDescribable {
	/**
	 * Mandatory attributes of an item.
	 */
	var $title, $description, $link;

	/**
	 * Optional attributes of an item.
	 */
	var $author, $authorEmail, $image, $category, $comments, $guid, $source, $creator;

	/**
	 * Publishing date of an item. May be in one of the following formats:
	 *
	 *	RFC 822:
	 *	"Mon, 20 Jan 03 18:05:41 +0400"
	 *	"20 Jan 03 18:05:41 +0000"
	 *
	 *	ISO 8601:
	 *	"2003-01-20T18:05:41+04:00"
	 *
	 *	Unix:
	 *	1043082341
	 */
	var $date;

	/**
	 * Add <enclosure> element tag RSS 2.0
	 * modified by : Mohammad Hafiz bin Ismail (mypapit@gmail.com)
	 *
	 *
	 * display :
	 * <enclosure length="17691" url="http://something.com/picture.jpg" type="image/jpeg" />
	 *
	 */
	var $enclosure;

	/**
	 * Any additional elements to include as an assiciated array. All $key => $value pairs
	 * will be included unencoded in the feed item in the form
	 *     <$key>$value</$key>
	 * Again: No encoding will be used! This means you can invalidate or enhance the feed
	 * if $value contains markup. This may be abused to embed tags not implemented by
	 * the FeedCreator class used.
	 */
	var $additionalElements = Array();

	// on hold
	// var $source;
}

class EnclosureItem extends HtmlDescribable {
	/*
	*
	* core variables
	*
	**/
	var $url,$length,$type;

	/*
	* For use with another extension like Yahoo mRSS
	* Warning :
	* These variables might not show up in
	* later release / not finalize yet!
	*
	*/
	var $width, $height, $title, $description, $keywords, $thumburl;

	var $additionalElements = Array();

}


/**
 * An FeedImage may be added to a FeedCreator feed.
 * @author Kai Blankenhorn <kaib@bitfolge.de>
 * @since 1.3
 */
class FeedImage extends HtmlDescribable {
	/**
	 * Mandatory attributes of an image.
	 */
	var $title, $url, $link;

	/**
	 * Optional attributes of an image.
	 */
	var $width, $height, $description;
}



/**
 * An HtmlDescribable is an item within a feed that can have a description that may
 * include HTML markup.
 */
class HtmlDescribable {
	/**
	 * Indicates whether the description field should be rendered in HTML.
	 */
	var $descriptionHtmlSyndicated;

	/**
	 * Indicates whether and to how many characters a description should be truncated.
	 */
	var $descriptionTruncSize;

	/**
	 * Returns a formatted description field, depending on descriptionHtmlSyndicated and
	 * $descriptionTruncSize properties
	 * @return    string    the formatted description
	 */
	function getDescription() {
		$descriptionField = new FeedHtmlField($this->description);
		$descriptionField->syndicateHtml = $this->descriptionHtmlSyndicated;
		$descriptionField->truncSize = $this->descriptionTruncSize;
		return $descriptionField->output();
	}

}



/**
 * An FeedHtmlField describes and generates
 * a feed, item or image html field (probably a description). Output is
 * generated based on $truncSize, $syndicateHtml properties.
 * @author Pascal Van Hecke <feedcreator.class.php@vanhecke.info>
 * @version 1.6
 */
class FeedHtmlField {
	/**
	 * Mandatory attributes of a FeedHtmlField.
	 */
	var $rawFieldContent;

	/**
	 * Optional attributes of a FeedHtmlField.
	 *
	 */
	var $truncSize, $syndicateHtml;

	/**
	 * Creates a new instance of FeedHtmlField.
	 * @param  $string: if given, sets the rawFieldContent property
	 */
	function FeedHtmlField($parFieldContent) {
		if ($parFieldContent) {
			$this->rawFieldContent = $parFieldContent;
		}
	}


	/**
	 * Creates the right output, depending on $truncSize, $syndicateHtml properties.
	 * @return string    the formatted field
	 */
	function output() {
		// when field available and syndicated in html we assume
		// - valid html in $rawFieldContent and we enclose in CDATA tags
		// - no truncation (truncating risks producing invalid html)
		if (!$this->rawFieldContent) {
			$result = "";
		}	elseif ($this->syndicateHtml) {
			$result = "<![CDATA[".$this->rawFieldContent."]]>";
		} else {
			if ($this->truncSize and is_int($this->truncSize)) {
				$result = FeedCreator::iTrunc(htmlspecialchars($this->rawFieldContent),$this->truncSize);
			} else {
				$result = htmlspecialchars($this->rawFieldContent);
			}
		}
		return $result;
	}

}



/**
 * UniversalFeedCreator lets you choose during runtime which
 * format to build.
 * For general usage of a feed class, see the FeedCreator class
 * below or the example above.
 *
 * @since 1.3
 * @author Kai Blankenhorn <kaib@bitfolge.de>
 */
class UniversalFeedCreator extends FeedCreator {

	var $_feed;

	function _setMIME($format) {
		switch (strtoupper($format)) {

			case "RSS":
			case "2.0":
				// fall through
			case "RSS2.0":
				header('Content-type: text/xml', true);
				break;

			case "ATOM":
				// fall through: always the latest ATOM version
			case "ATOM1.0":
				header('Content-type: application/xml', true);
				break;

			default:
			case "0.91":
				// fall through
			case "RSS0.91":
				header('Content-type: text/xml', true);
				break;
		}
	}

	function _setFormat($format) {
		switch (strtoupper($format)) {

			case "RSS":
			case "2.0":
				// fall through
			case "RSS2.0":
				$this->_feed = new RSSCreator20();
				break;

			case "0.91":
				// fall through
			case "RSS0.91":
				$this->_feed = new RSSCreator091();
				break;

			case "ATOM":
				// fall through: always the latest ATOM version
			case "ATOM1.0":
				$this->_feed = new AtomCreator10();
				break;

			default:
				$this->_feed = new RSSCreator091();
				break;
		}

		$vars = get_object_vars($this);
		foreach ($vars as $key => $value) {
			// prevent overwriting of properties "contentType", "encoding"; do not copy "_feed" itself
			if (!in_array($key, array("_feed", "contentType", "encoding"))) {
				$this->_feed->{$key} = $this->{$key};
			}
		}
	}

	/**
	 * Creates a syndication feed based on the items previously added.
	 *
	 * @see        FeedCreator::addItem()
	 * @param    string    format    format the feed should comply to. Valid values are:
	 *			"PIE0.1", "mbox", "RSS0.91", "RSS1.0", "RSS2.0", "OPML", "ATOM0.3", "HTML", "JS"
	 * @return    string    the contents of the feed.
	 */
	function createFeed($format = "RSS0.91") {
		$this->_setFormat($format);
		return $this->_feed->createFeed();
	}


   /**
	* Outputs feed to the browser - needed for on-the-fly feed generation (like it is done in WordPress, etc.)
	*
	* @param	format	string	format the feed should comply to. Valid values are:
    * 							"PIE0.1" (deprecated), "mbox", "RSS0.91", "RSS1.0", "RSS2.0", "OPML", "ATOM0.3".
	*/
   function outputFeed( $timezone , $format='RSS2.0' ) {
		$this->_setFormat( $format );
		$this->_setMIME( $format );
		$this->_feed->outputFeed( $timezone , $format );
   }

}


/**
 * FeedCreator is the abstract base implementation for concrete
 * implementations that implement a specific format of syndication.
 *
 * @abstract
 * @author Kai Blankenhorn <kaib@bitfolge.de>
 * @since 1.4
 */
class FeedCreator extends HtmlDescribable {

	/**
	 * Mandatory attributes of a feed.
	 */
	var $title, $description, $link;


	/**
	 * Optional attributes of a feed.
	 */
	var $syndicationURL, $image, $language, $copyright, $pubDate, $lastBuildDate, $editor, $editorEmail, $webmaster, $category, $docs, $ttl, $rating, $skipHours, $skipDays;

	/**
	* The url of the external xsl stylesheet used to format the naked rss feed.
	* Ignored in the output when empty.
	*/
	var $xslStyleSheet = "";


	/**
	 * @access private
	 */
	var $items = Array();


	/**
	 * This feed's MIME content type.
	 * @since 1.4
	 * @access private
	 */
	var $contentType = "application/xml";


	/**
	 * This feed's character encoding.
	 * @since 1.6.1
	 **/
	var $encoding = "UTF-8";


	/**
	 * Any additional elements to include as an assiciated array. All $key => $value pairs
	 * will be included unencoded in the feed in the form
	 *     <$key>$value</$key>
	 * Again: No encoding will be used! This means you can invalidate or enhance the feed
	 * if $value contains markup. This may be abused to embed tags not implemented by
	 * the FeedCreator class used.
	 */
	var $additionalElements = Array();


	/**
	 * Adds an FeedItem to the feed.
	 *
	 * @param object FeedItem $item The FeedItem to add to the feed.
	 * @access public
	 */
	function addItem($item) {
		$this->items[] = $item;
	}


	/**
	 * Truncates a string to a certain length at the most sensible point.
	 * First, if there's a '.' character near the end of the string, the string is truncated after this character.
	 * If there is no '.', the string is truncated after the last ' ' character.
	 * If the string is truncated, " ..." is appended.
	 * If the string is already shorter than $length, it is returned unchanged.
	 *
	 * @static
	 * @param string    string A string to be truncated.
	 * @param int        length the maximum length the string should be truncated to
	 * @return string    the truncated string
	 */
	function iTrunc($string, $length) {
		if (strlen($string)<=$length) {
			return $string;
		}

		$pos = strrpos($string,".");
		if ($pos>=$length-4) {
			$string = substr($string,0,$length-4);
			$pos = strrpos($string,".");
		}
		if ($pos>=$length*0.4) {
			return substr($string,0,$pos+1)." ...";
		}

		$pos = strrpos($string," ");
		if ($pos>=$length-4) {
			$string = substr($string,0,$length-4);
			$pos = strrpos($string," ");
		}
		if ($pos>=$length*0.4) {
			return substr($string,0,$pos)." ...";
		}

		return substr($string,0,$length-4)." ...";

	}


	/**
	 * Creates a comment indicating the generator of this feed.
	 * The format of this comment seems to be recognized by
	 * Syndic8.com.
	 */
	function _createGeneratorComment() {
		return "<!-- generator=\"".FEEDCREATOR_VERSION."\" -->\n";
	}


	/**
	 * Creates a string containing all additional elements specified in
	 * $additionalElements.
	 * @param	elements	array	an associative array containing key => value pairs
	 * @param indentString	string	a string that will be inserted before every generated line
	 * @return    string    the XML tags corresponding to $additionalElements
	 */
	function _createAdditionalElements($elements, $indentString="") {
		$ae = "";
		if (is_array($elements)) {
			foreach($elements AS $key => $value) {
				$ae.= $indentString."<$key>$value</$key>\n";
			}
		}
		return $ae;
	}

	function _createStylesheetReferences() {
		$xml = "";
		if ( isset( $this->cssStyleSheet ) ) $xml .= "<?xml-stylesheet href=\"".$this->cssStyleSheet."\" type=\"text/css\"?>\n";
		if ( isset( $this->xslStyleSheet ) ) $xml .= "<?xml-stylesheet href=\"".$this->xslStyleSheet."\" type=\"text/xsl\"?>\n";
		return $xml;
	}


	/**
	 * Builds the feed's text.
	 * @abstract
	 * @return    string    the feed's complete text
	 */
	function createFeed( $timezone ) {
	}

	/**
	 * Generate a filename for the feed cache file. The result will be $_SERVER["PHP_SELF"] with the extension changed to .xml.
	 * For example:
	 *
	 * echo $_SERVER["PHP_SELF"]."\n";
	 * echo FeedCreator::_generateFilename();
	 *
	 * would produce:
	 *
	 * /rss/latestnews.php
	 * latestnews.xml
	 *
	 * @return string the feed cache filename
	 * @since 1.4
	 * @access private
	 */
	function _generateFilename() {
		$fileInfo = pathinfo($_SERVER["PHP_SELF"]);
		return substr($fileInfo["basename"],0,-(strlen($fileInfo["extension"])+1)).".xml";
	}


	/**
	 * @since 1.4
	 * @access private
	 */
	function _redirect($filename) {
		// attention, heavily-commented-out-area

		// maybe use this in addition to file time checking
		//Header("Expires: ".date("r",time()+$this->_timeout));

		/* no caching at all, doesn't seem to work as good:
		Header("Cache-Control: no-cache");
		Header("Pragma: no-cache");
		*/

		// HTTP redirect, some feed readers' simple HTTP implementations don't follow it
		//Header("Location: ".$filename);

		Header("Content-Type: ".$this->contentType."; charset=".$this->encoding."; filename=".basename($filename));
		Header("Content-Disposition: inline; filename=".basename($filename));
		readfile($filename, "r");
		die();
	}

	/**
	 * Outputs this feed directly to the browser - for on-the-fly feed generation
	 * @since 1.7.2-mod
	 *
	 * still missing: proper header output - currently you have to add it manually
	 */
	function outputFeed( $timezone , $format='RSS2.0' ) {
		echo $this->createFeed( $timezone );
	}

}


/**
 * FeedDate is an internal class that stores a date for a feed or feed item.
 * Usually, you won't need to use this.
 */
class FeedDate {
	var $unix;

	/**
	 * Creates a new instance of FeedDate representing a given date.
	 * Accepts RFC 822, ISO 8601 date formats as well as unix time stamps.
	 * @param mixed $dateString optional the date this FeedDate will represent. If not specified, the current date and time is used.
	 */
	function FeedDate($dateString="") {
		if ($dateString=="") $dateString = date("r");

		if (is_numeric($dateString)) {
			$this->unix = $dateString;
			return;
		}
		if (preg_match("~(?:(?:Mon|Tue|Wed|Thu|Fri|Sat|Sun),\\s+)?(\\d{1,2})\\s+([a-zA-Z]{3})\\s+(\\d{4})\\s+(\\d{2}):(\\d{2}):(\\d{2})\\s+(.*)~",$dateString,$matches)) {
			$months = Array("Jan"=>1,"Feb"=>2,"Mar"=>3,"Apr"=>4,"May"=>5,"Jun"=>6,"Jul"=>7,"Aug"=>8,"Sep"=>9,"Oct"=>10,"Nov"=>11,"Dec"=>12);
			$this->unix = mktime($matches[4],$matches[5],$matches[6],$months[$matches[2]],$matches[1],$matches[3]);
			if (substr($matches[7],0,1)=='+' OR substr($matches[7],0,1)=='-') {
				$tzOffset = (substr($matches[7],0,3) * 60 + substr($matches[7],-2)) * 60;
			} else {
				if (strlen($matches[7])==1) {
					$oneHour = 3600;
					$ord = ord($matches[7]);
					if ($ord < ord("M")) {
						$tzOffset = (ord("A") - $ord - 1) * $oneHour;
					} elseif ($ord >= ord("M") AND $matches[7]!="Z") {
						$tzOffset = ($ord - ord("M")) * $oneHour;
					} elseif ($matches[7]=="Z") {
						$tzOffset = 0;
					}
				}
				switch ($matches[7]) {
					case "UT":
					case "GMT":	$tzOffset = 0;
				}
			}
			$this->unix += $tzOffset;
			return;
		}
		if (preg_match("~(\\d{4})-(\\d{2})-(\\d{2})T(\\d{2}):(\\d{2}):(\\d{2})(.*)~",$dateString,$matches)) {
			$this->unix = mktime($matches[4],$matches[5],$matches[6],$matches[2],$matches[3],$matches[1]);
			if (substr($matches[7],0,1)=='+' OR substr($matches[7],0,1)=='-') {
				$tzOffset = (substr($matches[7],0,3) * 60 + substr($matches[7],-2)) * 60;
			} else {
				if ($matches[7]=="Z") {
					$tzOffset = 0;
				}
			}
			$this->unix += $tzOffset;
			return;
		}
		$this->unix = 0;
	}





	/**
	 * Gets the date stored in this FeedDate as an RFC 822 date.
	 *
	 * @return a date in RFC 822 format
	 */
	function rfc822() {
		$d = new DateTime( "@" . $this->unix );

        return $d->format( 'r' );
	}

	/**
	 * Gets the date stored in this FeedDate as an ISO 8601 date.
	 *
	 * @return a date in ISO 8601 (RFC 3339) format
	 */
	function iso8601() {
		$d = new DateTime( "@" . $this->unix );

        return $d->format( 'c' );
	}


	/**
	 * Gets the date stored in this FeedDate as unix time stamp.
	 *
	 * @return a date as a unix time stamp
	 */
	function unix() {
		return $this->unix;
	}
}




/**
 * RSSCreator091 is a FeedCreator that implements RSS 0.91 Spec, revision 3.
 *
 * @see http://my.netscape.com/publish/formats/rss-spec-0.91.html
 * @since 1.3
 * @author Kai Blankenhorn <kaib@bitfolge.de>
 */
class RSSCreator091 extends FeedCreator {

	/**
	 * Stores this RSS feed's version number.
	 * @access private
	 */
	var $RSSVersion;

	function RSSCreator091() {
		$this->_setRSSVersion("0.91");
		$this->contentType = "application/rss+xml";
	}

	/**
	 * Sets this RSS feed's version number.
	 * @access private
	 */
	function _setRSSVersion($version) {
		$this->RSSVersion = $version;
	}

	/**
	 * Builds the RSS feed's text. The feed will be compliant to RDF Site Summary (RSS) 1.0.
	 * The feed will contain all items previously added in the same order.
	 * @return    string    the feed's complete text
	 */
	function createFeed( $timezone ) {
		$feed = "<?xml version=\"1.0\" encoding=\"".$this->encoding."\"?>\n";
		$feed.= $this->_createGeneratorComment();
		$feed.= $this->_createStylesheetReferences();
		$feed.= "<rss version=\"".$this->RSSVersion."\">\n";
		$feed.= "    <channel>\n";
		$feed.= "        <title>".FeedCreator::iTrunc(htmlspecialchars($this->title),100)."</title>\n";
		$this->descriptionTruncSize = 500;
		$feed.= "        <description>".$this->getDescription()."</description>\n";
		$feed.= "        <link>".htmlspecialchars($this->link)."</link>\n";

		$now = new DateTime( '@' . time() );

		$feed.= "        <lastBuildDate>".htmlspecialchars( $now->format( 'r' ) )."</lastBuildDate>\n";
		$feed.= "        <generator>".FEEDCREATOR_VERSION."</generator>\n";

		if ($this->image!=null) {
			$feed.= "        <image>\n";
			$feed.= "            <url>".$this->image->url."</url>\n";
			$feed.= "            <title>".FeedCreator::iTrunc(htmlspecialchars($this->image->title),100)."</title>\n";
			$feed.= "            <link>".htmlspecialchars($this->image->link)."</link>\n";
			if ($this->image->width!="") {
				$feed.= "            <width>".$this->image->width."</width>\n";
			}
			if ($this->image->height!="") {
				$feed.= "            <height>".$this->image->height."</height>\n";
			}
			if ($this->image->description!="") {
				$feed.= "            <description>".$this->image->getDescription()."</description>\n";
			}
			$feed.= "        </image>\n";
		}
		if ($this->language!="") {
			$feed.= "        <language>".$this->language."</language>\n";
		}
		if ($this->copyright!="") {
			$feed.= "        <copyright>".FeedCreator::iTrunc(htmlspecialchars($this->copyright),100)."</copyright>\n";
		}
		if ($this->editor!="") {
			$feed.= "        <managingEditor>".FeedCreator::iTrunc(htmlspecialchars($this->editor),100)."</managingEditor>\n";
		}
		if ($this->webmaster!="") {
			$feed.= "        <webMaster>".FeedCreator::iTrunc(htmlspecialchars($this->webmaster),100)."</webMaster>\n";
		}
		if ($this->pubDate!="") {
			$pubDate = new FeedDate($this->pubDate);
			$feed.= "        <pubDate>".htmlspecialchars($pubDate->rfc822())."</pubDate>\n";
		}
		if ($this->category!="") {
			$feed.= "        <category>".htmlspecialchars($this->category)."</category>\n";
		}
		if ($this->docs!="") {
			$feed.= "        <docs>".FeedCreator::iTrunc(htmlspecialchars($this->docs),500)."</docs>\n";
		}
		if ($this->ttl!="") {
			$feed.= "        <ttl>".htmlspecialchars($this->ttl)."</ttl>\n";
		}
		if ($this->rating!="") {
			$feed.= "        <rating>".FeedCreator::iTrunc(htmlspecialchars($this->rating),500)."</rating>\n";
		}
		if ($this->skipHours!="") {
			$feed.= "        <skipHours>".htmlspecialchars($this->skipHours)."</skipHours>\n";
		}
		if ($this->skipDays!="") {
			$feed.= "        <skipDays>".htmlspecialchars($this->skipDays)."</skipDays>\n";
		}
		$feed.= $this->_createAdditionalElements($this->additionalElements, "    ");

		for ($i=0;$i<count($this->items);$i++) {
			$feed.= "        <item>\n";
			$feed.= "            <title>".FeedCreator::iTrunc(htmlspecialchars(strip_tags($this->items[$i]->title)),100)."</title>\n";
			$feed.= "            <link>".htmlspecialchars($this->items[$i]->link)."</link>\n";
			$feed.= "            <description>".$this->items[$i]->getDescription()."</description>\n";

			if ($this->items[$i]->author!="") {
				$feed.= "            <author>".htmlspecialchars($this->items[$i]->author)."</author>\n";
			}
			/*
			// on hold
			if ($this->items[$i]->source!="") {
					$feed.= "            <source>".htmlspecialchars($this->items[$i]->source)."</source>\n";
			}
			*/
			if ($this->items[$i]->category!="") {
				$feed.= "            <category>".htmlspecialchars($this->items[$i]->category)."</category>\n";
			}
			if ($this->items[$i]->comments!="") {
				$feed.= "            <comments>".htmlspecialchars($this->items[$i]->comments)."</comments>\n";
			}
			if ($this->items[$i]->date!="") {
			$itemDate = new FeedDate($this->items[$i]->date);
				$feed.= "            <pubDate>".htmlspecialchars($itemDate->rfc822())."</pubDate>\n";
			}
			if ($this->items[$i]->guid!="") {
				$feed.= "            <guid>".htmlspecialchars($this->items[$i]->guid)."</guid>\n";
			}
			$feed.= $this->_createAdditionalElements($this->items[$i]->additionalElements, "        ");

			if ($this->RSSVersion == "2.0" && $this->items[$i]->enclosure != NULL)
				{
				                $feed.= "            <enclosure url=\"";
				                $feed.= $this->items[$i]->enclosure->url;
				                $feed.= "\" length=\"";
				                $feed.= $this->items[$i]->enclosure->length;
				                $feed.= "\" type=\"";
				                $feed.= $this->items[$i]->enclosure->type;
				                $feed.= "\"/>\n";
		            	}



			$feed.= "        </item>\n";
		}

		$feed.= "    </channel>\n";
		$feed.= "</rss>\n";
		return $feed;
	}
}


/**
 * RSSCreator20 is a FeedCreator that implements RDF Site Summary (RSS) 2.0.
 *
 * @see http://backend.userland.com/rss
 * @since 1.3
 * @author Kai Blankenhorn <kaib@bitfolge.de>
 */
class RSSCreator20 extends RSSCreator091 {

    function RSSCreator20() {
        parent::_setRSSVersion("2.0");
    }

}


/**
 * AtomCreator10 is a FeedCreator that implements the atom specification,
 * as in http://www.atomenabled.org/developers/syndication/atom-format-spec.php
 * Please note that just by using AtomCreator10 you won't automatically
 * produce valid atom files. For example, you have to specify either an editor
 * for the feed or an author for every single feed item.
 *
 * Some elements have not been implemented yet. These are (incomplete list):
 * author URL, item author's email and URL, item contents, alternate links,
 * other link content types than text/html. Some of them may be created with
 * AtomCreator10::additionalElements.
 *
 * @see FeedCreator#additionalElements
 * @since 1.7.2-mod (modified)
 * @author Mohammad Hafiz Ismail (mypapit@gmail.com)
 */
 class AtomCreator10 extends FeedCreator {

	function AtomCreator10() {
		$this->contentType = "application/atom+xml";
		$this->encoding = "utf-8";
	}

	function createFeed( $timezone ) {
		$feed = "<?xml version=\"1.0\" encoding=\"".$this->encoding."\"?>\n";
		$feed.= $this->_createGeneratorComment();
		$feed.= $this->_createStylesheetReferences();
		$feed.= "<feed xmlns=\"http://www.w3.org/2005/Atom\"";
		if ($this->language!="") {
			$feed.= " xml:lang=\"".$this->language."\"";
		}
		$feed.= ">\n";
		$feed.= "    <title>".htmlspecialchars($this->title)."</title>\n";
		$feed.= "    <subtitle type=\"html\">".htmlspecialchars($this->description)."</subtitle>\n";
		$feed.= "    <link rel=\"alternate\" type=\"text/html\" href=\"".htmlspecialchars($this->link)."\"/>\n";
		$feed.= "    <id>".htmlspecialchars($this->link)."</id>\n";
		$now = new DateTime( '@' . time() );
		$feed.= "    <updated>".htmlspecialchars( $now->format( 'c' ) )."</updated>\n";
		if ($this->editor!="") {
			$feed.= "    <author>\n";
			$feed.= "        <name>".$this->editor."</name>\n";
			if ($this->editorEmail!="") {
				$feed.= "        <email>".$this->editorEmail."</email>\n";
			}
			$feed.= "    </author>\n";
		}
		$feed.= "    <generator>".FEEDCREATOR_VERSION."</generator>\n";
		$feed.= "<link rel=\"self\" type=\"application/atom+xml\" href=\"". htmlspecialchars($this->syndicationURL) . "\" />\n";
		$feed.= $this->_createAdditionalElements($this->additionalElements, "    ");
		for ($i=0;$i<count($this->items);$i++) {
			$feed.= "    <entry>\n";
			$feed.= "        <title>".htmlspecialchars(strip_tags($this->items[$i]->title))."</title>\n";
			$feed.= "        <link rel=\"alternate\" type=\"text/html\" href=\"".htmlspecialchars($this->items[$i]->link)."\"/>\n";
			if ($this->items[$i]->date=="") {
				$this->items[$i]->date = time();
			}
			$itemDate = new FeedDate($this->items[$i]->date);
			$feed.= "        <published>".htmlspecialchars($itemDate->iso8601())."</published>\n";
			$feed.= "        <updated>".htmlspecialchars($itemDate->iso8601())."</updated>\n";
			$feed.= "        <id>".htmlspecialchars($this->items[$i]->link)."</id>\n";
			$feed.= $this->_createAdditionalElements($this->items[$i]->additionalElements, "        ");
			if ($this->items[$i]->author!="") {
				$feed.= "        <author>\n";
				$feed.= "            <name>".htmlspecialchars($this->items[$i]->author)."</name>\n";
				$feed.= "        </author>\n";
			}
			if ($this->items[$i]->description!="") {
				$feed.= "        <summary type=\"html\">".htmlspecialchars($this->items[$i]->description)."</summary>\n";
			}
			if ($this->items[$i]->enclosure != NULL) {
			$feed.="        <link rel=\"enclosure\" href=\"". $this->items[$i]->enclosure->url ."\" type=\"". $this->items[$i]->enclosure->type."\"  length=\"". $this->items[$i]->enclosure->length . "\" />\n";
			}
			$feed.= "    </entry>\n";
		}
		$feed.= "</feed>\n";
		return $feed;
	}


}

?>
