class php {
  package { ['php5.6', 'php5.6-bcmath', 'php5.6-mcrypt', 'php5.6-curl', 'php5.6-cli', 'php5.6-mysql', 'php5.6-gd', 'php5.6-intl', 'php5.6-xsl', 'php5.6-xdebug', 'php5.6-mbstring', 'php5.6-zip', 'php5.6-fpm']:
    ensure => present,
    notify => Service['nginx'];
  }

  php::module{ ['20-xdebug.ini']: }
}
