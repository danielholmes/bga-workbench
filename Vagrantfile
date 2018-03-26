# -*- mode: ruby -*-
# vi: set ft=ruby :

Vagrant.configure("2") do |config|
  project_name = "bga-workbench"

  # Base box to build off, and download URL for when it doesn't exist on the user's system already
  config.vm.box = "ubuntu/xenial64"
  config.vm.box_url = "http://cloud-images.ubuntu.com/xenial/current/xenial-server-cloudimg-amd64-vagrant.box"

  config.vm.provider "virtualbox" do |v|
    v.name = project_name
    v.memory = 2048
    v.customize [ "modifyvm", :id, "--uartmode1", "disconnected" ]
  end

  # Share an additional folder to the guest VM. The first argument is
  # an identifier, the second is the path on the guest to mount the
  # folder, and the third is the path on the host to the actual folder.
  #config.vm.share_folder project_name, "/home/ubuntu/" + project_name, "."
  config.vm.synced_folder "./", "/home/ubuntu/" + project_name

  # Enable provisioning with a shell script.
  config.vm.provision :shell, :path => "etc/install.sh", :args => project_name
end
