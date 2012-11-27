<?php

/**
 * Icount
 * iCount.co.il API PHP Class
 * @author Lior Broshi - Binternet, lior@binternet.co.il
 * @copyright 2012
 * @version 0.1b
 *
 *  MIT License
 * ===========
 * Copyright (c) 2012 Lior Broshi, lior@binternet.co.il
 * Permission is hereby granted, free of charge, to any person obtaining a
 * copy of this software and associated documentation files (the "Software"),
 * to deal in the Software without restriction, including without limitation
 * the rights to use, copy, modify, merge, publish, distribute, sublicense,
 * and/or sell copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
 * DEALINGS IN THE SOFTWARE.

 */
class Icount {
	
	/*
	|--------------------------------------------------------------------------
	| Settings
	|--------------------------------------------------------------------------
	*/
	const COMPANY_ID = '';					 # Company Identifier
	const USERNAME = '';		 			 # Username
	const PASSWORD = '';					 # Password
	
	
	#### DO NOT EDIT BELOW ####
	
	const API_URL = 'https://www.icount.co.il/api/create_doc.php';
	private $fields = array();
	private $client_details_set = FALSE;
	public $ssl_verify_peer = TRUE;
	
	/**
	 * Icount::__construct()
	 * 
	 * @return void
	 */
	function __construct() {
		
		# We ust have cURL
		if ( ! function_exists('curl_init') ) {
		  throw new Exception('iCount API needs the cURL PHP extension.');
		}

		ini_set("set_time_limit", "5");  
		ini_set("max_execution_time", "5");  
	}
	
	/**
	 * Icount::set_fields()
	 * $fields setter
	 * @param mixed $fields
	 * @return void
	 */
	function set_fields($fields) {
		$this->fields = $fields;
	}
	
	/**
	 * Icount::get_fields()
	 * $fields getter
	 * @return void
	 */
	function get_fields() {
		return $this->fields;
	}
	
	/**
	 * Icount::set_total()
	 * Sets the total sum of the document
	 * @param mixed $sum				Total Sum
	 * @param mixed $vat				VAT Rate
	 * @param mixed $sum_with_vat		Total Sum with VAT
	 * @return void
	 */
	function set_total($total_sum,$vat,$sum_with_vat) {
		$this->fields['totalsum'] = $total_sum;
		$this->fields['totalvat'] = $vat;
		$this->fields['totalwithvat'] = $sum_with_vat;
	}
	
	/**
	 * Icount::set_document_date()
	 * Sets the document date
	 * @param mixed $date		Date in YYYYMMDD Format
	 * @return void
	 */
	function set_document_date($date) {
		$this->fields['dateissued'] = $date;
	}
	
	/**
	 * Icount::set()
	 * Sets a field in the $fields array
	 * @param mixed $key			Key
	 * @param mixed $value			Value
	 * @return void
	 */
	function set($key,$value) {
		$this->fields[$key] = $value;
	}
	
	/**
	 * Icount::set_document_type()
	 * Sets document type
	 * @param string $type		invrec | deal
	 * @return void
	 */
	function set_document_type($type='invrec') {
		$this->fields['docType'] = $type;
	}
	
	/**
	 * Icount::set_VAT()
	 * Sets VAT rate
	 * @param string $vat
	 * @return void
	 */
	function set_VAT($vat='17') {
		$this->fields['maampercent'] = $vat;
	}
	
	/**
	 * Icount::set_currency()
	 * 
	 * @param string $currency
	 * @return void
	 */
	function set_currency($currency='NIS') {
		$currencies = array('EUR'=>1,'USD'=>2,'YEN'=>3,'GBP'=>4,'NIS'=>5,'SGP'=>6,'CAD'=>7,'RUB'=>8,'NZD'=>9,'AUD'=>10,'KES'=>11,'BRL'=>12);
		if ( array_key_exists($currency,$currencies) ) {
			$this->fields['currency'] = $currencies[$currency];
		} else {
			# Default Value is NIS
			$this->fields['currency'] = 5;
		}
	}
	
	/**
	 * Icount::set_items()
	 * Sets all items at once
	 * @param array $description			Descriptions
	 * @param array $price					Prices
	 * @param array $quantity				Quantities
	 * @return void
	 */
	function set_items($descriptions,$prices,$quantities) {
		
		if ( 
			! is_array($descriptions) OR empty($descriptions) ||
			! is_array($prices) OR empty($prices) 			  ||
			! is_array($quantities) OR empty($quantities)
		 ) 
		 {
		 	throw new Exception(__METHOD__ . ' must get 3 array parameters');
		 }
		 
		 # Turn these arrays into iCount structure
		 $array = array();
		 foreach ( $descriptions as $key => $value ) {
		 	$array["desc[{$key}]"] = $value;
		 	$array["unitprice[{$key}]"] = $prices[$key];
		 	$array["quantity[{$key}]"] = $quantities[$key];
		 }
		 
		 # Merge into $fields
		 $this->fields = array_merge($this->fields,$array);
	}
	
	/**
	 * Icount::set_client()
	 * Sets Client Info
	 * @param mixed $name					Name
	 * @param mixed $street					Street
	 * @param mixed $street_number			Street Number
	 * @param mixed $city					City
	 * @param mixed $country				Country
	 * @param mixed $zipcode				Zipcode
	 * @return void
	 */
	function set_client($name,$street='',$street_number='',$city='',$country='',$zipcode='') {
		
		if ( empty($name) ) {
			throw new Exception('עלייך לספק פרטי לקוח');
		}
		
		$this->fields['clientname'] = $name;
		$this->fields['client_street'] = $street;
		$this->fields['client_street_number'] = $street_number;
		$this->fields['client_city'] = $city;
		$this->fields['client_country'] = $country;
		$this->fields['client_zip'] = $zipcode;

		$this->client_details_set = TRUE;

	}
	
	/**
	 * Icount::set_document_comment()
	 * Sets the comment for the document
	 * @param string $text
	 * @return void
	 */
	function set_document_comment($text='') {
		$this->fields['hwc'] = $text;
	}
	
	/**
	 * Icount::add_item()
	 * Adds an item to the document
	 * @param mixed $name			Item Name
	 * @param mixed $price			Item Price
	 * @param integer $quantity		Item Quantity	
	 * @return void
	 */
	function add_item($name,$price,$quantity=1) {
		
		$items_keys = array();
		# Search for the next corresponding key only if we already have some items
		if ( array_key_exists('desc[0]',$this->fields) ) {
			foreach ( $this->fields as $key => $value ) {
				if ( preg_match('/desc\[\d\]/',$key) ) {
					# Item description entry, now lets extract the counter.
					$items_keys[] = (int)str_replace(array('desc[',']'),'',$key);
				}
			}
			# Get highest
			$highest_key = max($items_keys);
			$current_key = (int)$highest_key + 1;
		} else {
			# Does not exists
			$current_key = 0;
		}

		# Push into $fields
		$this->fields["desc[{$current_key}]"] = $name;
		$this->fields["unitprice[{$current_key}]"] = $price;
		$this->fields["quantity[{$current_key}]"] = $quantity;

	}

	/**
	 * Icount::clear()
	 * Clears the $fields array
	 * Good to use when we work with API inside a loop.
	 * @return void
	 */
	function clear() {
		
		# Fields to keep
		$keep_fields = array('compID','user','pass');
		foreach ( $this->fields as $key => $value ) {
			
			if ( in_array($key,$keep_fields) ) {
				continue;
			}
			
			# Reset
			$this->fields[$key] = '';
		}
		
	}
	
	/**
	 * Icount::create()
	 * Creates a document
	 * @return void
	 */
	function create() {

		/*
		|--------------------------------------------------------------------------
		| Validations
		|--------------------------------------------------------------------------
		*/
		if ( empty($this->fields) OR !is_array($this->fields) ) {
			throw new Exception('לא ניתן לבצע קריאה ללא הגדרת פרמטרים');
		}
		if ( empty($this->fields['docType']) ) {
			throw new Exception('לא הוגדר סוג מסמך');
		}
		if ( $this->client_details_set == FALSE ) {
			throw new Exception('עלייך לספק פרטי לקוח');
		}
		if ( ! isset($this->fields['desc[0]']) OR empty($this->fields['desc[0]']) ) {
			throw new Exception('חובה להגדיר לפחות פריט אחד');
		}

		/*
		|--------------------------------------------------------------------------
		| Set Credentials
		|--------------------------------------------------------------------------
		*/

		$this->fields['compID'] = self::COMPANY_ID;
		$this->fields['user'] = self::USERNAME;
		$this->fields['pass'] = self::PASSWORD;
		
		# Add issue date just incase we didn't supply one
		if ( ! isset($this->fields['dateissued']) ) {
			$this->fields['dateissued'] = date('Ymd');
		}
		
		# Add validity date just incase we didn't supply one
		if ( ! isset($this->fields['validity']) ) {
			$this->fields['validity'] = date('Ymd');
		}
		
		/*
		|--------------------------------------------------------------------------
		| Make Request
		|--------------------------------------------------------------------------
		*/
		
		$string = '';
		foreach( $this->fields as $key => $val ) {  
			$string .= "$key=$val&";  
		}
		# Trim last `&`
		$string = rtrim($string,'&');
		
		$ch = curl_init();  
		curl_setopt($ch, CURLOPT_URL,self::API_URL);  
		curl_setopt($ch, CURLOPT_POST, 1);  
		
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE); // meant for debugging
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->ssl_verify_peer);
		
		curl_setopt($ch, CURLOPT_POSTFIELDS,"$string");  
		#echo "-------------------------------------\n";  
		$return = curl_exec ($ch);  
		#echo "-------------------------------------\n";  
		curl_close ($ch);  
		
		var_dump($return);
	}
}