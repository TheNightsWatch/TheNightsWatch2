# == Define: module
#
# Adds php module file.
#
define php::module() {
  file { "/etc/php/5.6/fpm/conf.d/${name}":
    source  => "puppet:///modules/php/${name}",
    require => [Package['nginx'], Package['php5.6-fpm'], Package['php5.6-xdebug']],
    notify  => Service['nginx'];
  }
}
