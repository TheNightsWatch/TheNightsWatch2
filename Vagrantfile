domain = "tnw.local"

# Vagrantfile API/syntax version. Don't touch unless you know what you're doing!
VAGRANTFILE_API_VERSION = "2"

Vagrant.configure(VAGRANTFILE_API_VERSION) do |config|
    config.vm.box = "ubuntu/trusty64"
    config.vm.hostname = domain
    config.vm.network "private_network", type: "dhcp"

    config.hostmanager.enabled = true
    config.hostmanager.manage_host = true
    config.hostmanager.ignore_private_ip = true
    config.hostmanager.include_offline = true
    cached_addresses = {}
    ipaddr = ""
    config.hostmanager.ip_resolver = proc do |vm, resolving_vm|
        if cached_addresses[vm.name].nil?
            if hostname = (vm.ssh_info && vm.ssh_info[:host])
                vm.communicate.execute("/sbin/ifconfig eth1 | grep 'inet addr' | tail -n 1 | egrep -o '[0-9\.]+' | head -n 1 2>&1") do |type, contents|
                  cached_addresses[vm.name] = contents.split("\n").first[/(\d+\.\d+\.\d+\.\d+)/, 1]
                  ipaddr = cached_addresses[vm.name]
                end
            end
        end
        cached_addresses[vm.name]
    end

    config.vm.provider "virtualbox" do |vb|
        vb.memory = "2048"
    end

    config.vm.synced_folder ".", "/var/www/tnw.local", type: "nfs"

    config.vm.provision :puppet do |puppet|
        puppet.manifests_path = 'puppet/manifests'
        puppet.manifest_file = 'site.pp'
        puppet.module_path = 'puppet/modules'
        puppet.facter = {
            'hostname' => domain,
            'ipaddr' => cached_addresses[ipaddr],
        }
    end
end
