# == Class: baseconfig
#
# Performs initial configuration tasks for all Vagrant boxes.
#
class composer {
  package { ['curl']:
    ensure => present;
  }

  exec { 'install composer':
    command => '/usr/bin/curl -sS https://getcomposer.org/installer | /usr/bin/sudo -H /usr/bin/php -- --install-dir=/usr/local/bin --filename=composer',
    require => [Package['php5.6'], Package['curl']];
  }
}
