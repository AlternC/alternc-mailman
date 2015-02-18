alternc-mailman is now providing you with pre-compiled binaries for mailman cgi wrappers.

since we are now using apache-mpm-itk, which allows us to launch http vhosts as any unix user-id, 
we had a problem with mailman cgi-bin scripts that, before going su "list", is checking that
its current gid is not above 100 and is not "nobody" 
(for an unknown reason)

So we compiled amd64 and x86 version of those wrappers with the patch attached. 
You can build your own if you want, but since we are replacing files from mailman package, 
we copy them (overwriting mailman package's files) during alternc.install...

If you want to build your own mailman binaries proceed as follow:

# if you have the mailman-2.1.15.patch file in /tmp/
echo "deb-src http://debian.octopuce.fr/debian wheezy main contrib non-free" >>/etc/apt/sources.list
apt-get update
apt-get install debuild
apt-get source mailman
cd mailman-2.1.15
cd src
patch -p0 </tmp/mailman-2.1.15.patch
cd ..
debuild

you will obtain the binaries in src/
copy them into /usr/lib/cgi-bin/mailman in your server.

