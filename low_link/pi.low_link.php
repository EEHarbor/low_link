<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// include config file
include PATH_THIRD.'low_link/config.php';

$plugin_info = array(
	'pi_name'          => LOW_LINK_NAME,
	'pi_version'       => LOW_LINK_VERSION,
	'pi_author'        => 'Lodewijk Schutte ~ Low',
	'pi_author_url'    => LOW_LINK_DOCS,
	'pi_description'   => 'Search text for link to other entry using Wiki syntax',
	'pi_usage'         => Low_link::usage()
);

/**
* Low Link Plugin class
*
* @package         low-link-ee2_addon
* @version         1.0.0
* @author          Lodewijk Schutte ~ Low <low@loweblog.com>
* @link            http://loweblog.com/software/low-link/
*/
class Low_link {

	// --------------------------------------------------------------------
	// CLASS CONSTANTS
	// --------------------------------------------------------------------

	const LD = '[[';
	const RD = ']]';
	const MD = ' | ';

	// --------------------------------------------------------------------
	// PROPERTIES
	// --------------------------------------------------------------------

	/**
	* Plugin return data
	*
	* @access      public
	* @var         string
	*/
	public $return_data;

	// --------------------------------------------------------------------

	/**
	* ExpressionEngine object
	*
	* @access      private
	* @var         object
	*/
	private $EE;

	// --------------------------------------------------------------------
	// METHODS
	// --------------------------------------------------------------------

	/**
	* Constructor, sets global EE object
	*
	* @access      public
	* @return      void
	*/
	public function __construct()
	{
		$this->EE =& get_instance();
	}

	// --------------------------------------------------------------------

	/**
	* Search text for given links
	*
	* @access      public
	* @return      string
	*/
	public function apply()
	{
		// --------------------------------------
		// Initiate some variables used later on
		// --------------------------------------

		$words = $lookup = $found = array();

		// --------------------------------------
		// Get tagdata to search through
		// --------------------------------------

		$haystack = $this->EE->TMPL->tagdata;

		// --------------------------------------
		// Get tag parameters
		// --------------------------------------

		foreach (array('site', 'channel', 'field', 'tag') AS $attr)
		{
			$$attr = $this->EE->TMPL->fetch_param($attr);
		}

		// --------------------------------------
		// Compose regex used to search haystack
		// --------------------------------------

		$pattern = '#' .preg_quote(self::LD). '(.*?)' .preg_quote(self::RD). '#';

		// --------------------------------------
		// Search text
		// --------------------------------------

		if (preg_match_all($pattern, $haystack, $matches))
		{
			// Loop through matches
			foreach ($matches[0] AS $i => $match)
			{
				// See if the linked word is different from the search word: [[Search word | linked word]]
				$word = explode(self::MD, $matches[1][$i], 2);
				
				// Set linked word to search word if not explicitly given
				if (count($word) == 1)
				{
					$word[1] = $word[0];
				}

				// save to words and lookup array
				$words[$match] = $word;
				$lookup[] = $word[0];
			}
		}
		else
		{
			// Nothing found, just return without doing anything
			return $haystack;
		}

		// --------------------------------------
		// Which site?
		// --------------------------------------

		if ($site)
		{
			$query = $this->EE->db->select('site_id')->from('sites')->where('site_name', $site)->get();
			$site_id = $query->num_rows() ? $query->row('site_id') : $this->EE->config->item('site_id');
		}
		else
		{
			$site_id = $this->EE->config->item('site_id');
		}

		// --------------------------------------
		// Determine which fields to select from DB
		// --------------------------------------

		$sql_select = array('t.entry_id', 't.url_title');

		if (isset($this->EE->session->cache['channel'][$site_id][$field]))
		{
			$sql_select[] = 'd.field_id_'.$this->EE->session->cache['channel'][$site_id][$field].' AS word';
			$data_join = TRUE;
		}
		else
		{
			$sql_select[] = 't.title AS word';
			$data_join = FALSE;
		}

		// --------------------------------------
		// Query DB to lookup words
		// --------------------------------------
		
		$this->EE->db->select(implode(',', $sql_select))
		              ->from('channel_titles t')
		              ->where('t.site_id', $site_id)
		              ->where_in('t.title', $lookup);
		
		// Filter words by channel
		if ($channel)
		{
			$this->EE->db->join('channels c', 'c.channel_id = t.channel_id')
			             ->where('channel_name', $channel);
		}

		// Join exp_channel_data if necessary
		if ($data_join)
		{
			$this->EE->db->join('channel_data d', 'd.entry_id = t.entry_id');
		}
		
		$query = $this->EE->db->get();

		// Get results and put them in the Found array for later reference
		foreach ($query->result_array() AS $row)
		{
			$found[strtolower($row['word'])] = $row;
		}

		// --------------------------------------
		// Get tag and tag attrinbutes
		// --------------------------------------

		// Tag defaults to <a>
		$tag = ($tag) ? $tag : 'a';

		// Init attributes array
		$tagparams = array();

		// Look for tag:something parameters
		foreach ($this->EE->TMPL->tagparams AS $key => $val)
		{
			if (substr($key, 0, 4) == 'tag:')
			{
				$tagparams[substr($key, 4)] = $val;
			}
		}

		// --------------------------------------
		// Loop through words and replace them
		// --------------------------------------

		foreach ($words AS $marker => $word)
		{
			$lower_word = strtolower($word[0]);

			if (array_key_exists($lower_word, $found))
			{
				$attrs = '';

				foreach ($tagparams AS $key => $val)
				{
					// Replace markers in values with current entry_id and url_title
					$val = str_replace(
						array('%%entry_id%%', '%%url_title%%'),
						array($found[$lower_word]['entry_id'], $found[$lower_word]['url_title']),
						$val
					);

					// create url
					if (in_array($key, array('href', 'src')) &&  ! (substr($val, 0, 4) == 'http' || substr($val, 0, 1) == '/'))
					{
						$val = $this->EE->functions->create_url($val);
					}
					
					$attrs .= ' '.$key.'="'.$val.'"';
				}

				// Create replacement tag
				$replacement = "<{$tag}{$attrs}>{$word[1]}</{$tag}>";
			}
			else
			{
				$replacement = $word[1];
			}

			$haystack = str_replace($marker, $replacement, $haystack);
		}

		// --------------------------------------
		// Parse template vars
		// --------------------------------------

		$this->return_data = $haystack;

		// --------------------------------------
		// Like a movie, like a style
		// --------------------------------------

		return $this->return_data;
	}

	// --------------------------------------------------------------------

	/**
	* Plugin usage
	*
	* @return	string
	*/
	public function usage()
	{
		return <<<EOF
			{exp:low_link:apply channel="thesaurus" tag:href="/thesaurus/%%url_title%%/" tag:class="low_link word-%%entry_id%%"}
				<p>
					Lorem ipsum dolor sit amet, [[consectetur]] adipisicing elit,
					sed do eiusmod tempor incididunt ut labore et dolore magna
					aliqua. Ut enim ad minim veniam, quis nostrud exercitation
					[[ullamco laboris]] nisi ut aliquip ex ea commodo consequat.
					Duis aute irure dolor in reprehenderit in voluptate velit
					esse cillum dolore eu fugiat nulla pariatur. Excepteur
					sintoccaecat cupidatat non proident, sunt in culpa qui
					officia deserunt mollit anim id est [[laborum | something else]].
				<p>
			{/exp:low_link:apply}
EOF;
	}

	// --------------------------------------------------------------------

}

/* End of file pi.low_link.php */