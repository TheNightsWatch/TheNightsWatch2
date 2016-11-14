# == Class: baseconfig
#
# Performs initial configuration tasks for all Vagrant boxes.
#
class nginx {
  package {['nginx']:
    ensure => present;
  }

  service {'nginx':
    ensure => running,
    require => Package['nginx'];
  }

  file { "/etc/nginx/sites-enabled/tnw.local":
    source  => "puppet:///modules/nginx/tnw.local",
    require => Package['nginx'],
    notify  => Service['nginx'];
  }
}
