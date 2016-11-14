# == Class: baseconfig

class baseconfig {
  exec {'Adding php 5.6 repository':
    command => '/usr/bin/add-apt-repository ppa:ondrej/php',
    before => Exec['apt-get update'];
  }

  exec { 'apt-get update' :
    command => '/usr/bin/apt-get update'
  }

  file {
    '/home/vagrant/.bashrc':
      owner => 'vagrant',
      group => 'vagrant',
      mode => '0644',
      source => 'puppet:///modules/baseconfig/bashrc';
  }
}
