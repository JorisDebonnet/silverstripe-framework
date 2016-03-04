<?php

/**
 * A simple extension to dropdown field, pre-configured to list countries.
 * It will default to the country of the current visitor.
 *
 * To disable any default values, use this:
 * <code>
 * $countryField = new CountryDropdownField('ExampleField', 'Example Field');
 * $countryField->config()->default_to_locale = false;
 * $countryField->config()->default_country = '';
 * $countryField->setHasEmptyDefault(true);
 * </code>
 *
 * @package forms
 * @subpackage fields-relational
 */
class CountryDropdownField extends DropdownField {
	/**
	 * Use alpha-3 country codes instead of alpha-2 country codes as option values?
	 * @var bool
	 */
	private static $alpha3 = false;

	/**
	 * Should we default the dropdown to the region determined from the user's locale?
	 * @var bool
	 */
	private static $default_to_locale = true;

	/**
	 * The region code to default to if default_to_locale is set to false, or we can't determine a region from a locale
	 *  @var string
	 */
	private static $default_country = 'NZ';

	protected $extraClasses = array('dropdown');

	/**
	 * Get the locale of the Member, or if we're not logged in or don't have a locale, use the default one
	 * @return string
	 */
	protected function locale() {
		if (($member = Member::currentUser()) && $member->Locale) return $member->Locale;
		return i18n::get_locale();
	}

	public function __construct($name, $title = null, $source = null, $value = "", $form=null) {
		if(!is_array($source)) {
			// Get a list of countries from Zend
			$source = Zend_Locale::getTranslationList('territory', $this->locale(), 2);

			// If so configured, change the alpha-2 country code keys into alpha-3 country code keys
			if($this->config()->alpha3=1) {
				$map = Zend_Locale::getTranslationList('Alpha3ToTerritory');
				$tmp = array();
				foreach($source as $k=>$v) {
					if(!empty($map[$k])) $tmp[$map[$k]] = $v;
				}
				$source = $tmp;
			}

			// We want them ordered by display name, not country code

			// PHP 5.3 has an extension that sorts UTF-8 strings correctly
			if (class_exists('Collator') && ($collator = Collator::create($this->locale()))) {
				$collator->asort($source);
			}
			// Otherwise just put up with them being weirdly ordered for now
			else {
				asort($source);
			}

			// We don't want "unknown country" as an option
			unset($source['ZZ'], $source['ZZZ']);
		}

		parent::__construct($name, ($title===null) ? $name : $title, $source, $value, $form);
	}

	public function Field($properties = array()) {
		$source = $this->getSource();

		if(!$this->getHasEmptyDefault()){if (!$this->value || !isset($source[$this->value])) {
			if ($this->config()->default_to_locale && $this->locale()) {
				$locale = new Zend_Locale();
				$locale->setLocale($this->locale());
				$this->value = $locale->getRegion();
			if ($this->config()->alpha3=1) {
					$this->value = Zend_Locale::getTranslation($this->value, 'Alpha3ToTerritory');
				}}
		}

		if (!$this->value || !isset($source[$this->value])) {
			$this->value = $this->config()->default_country;
			if ($this->config()->alpha3=1) {
				$this->value = Zend_Locale::getTranslation($this->value, 'Alpha3ToTerritory');
			}
		}

		return parent::Field();
	}
}
