# == Class: mysql
#
# Performs initial configuration tasks for all Vagrant boxes.
#
class mysql {
  package { ['mysql-server-5.6']:
    ensure => present;
  }

  service { 'mysql':
    ensure => running,
    require => Package['mysql-server-5.6'];
  }

  file { '/etc/mysql/my.cnf':
    source  => 'puppet:///modules/mysql/my.cnf',
    require => Package['mysql-server-5.6'],
    notify  => Service['mysql'];
  }

  exec { 'set-mysql-password':
    unless  => 'mysqladmin -uroot -proot status',
    command => "mysqladmin -uroot password root",
    path    => ['/bin', '/usr/bin'],
    require => Service['mysql'];
  }

  exec { 'open-hostmachine-access':
    command => 'mysql -uroot -proot -e "GRANT ALL ON *.* to root@\'%\' IDENTIFIED BY \'root\'; FLUSH PRIVILEGES"',
    path    => ['/bin', '/usr/bin'],
    require => Exec['set-mysql-password'];
  }
}
