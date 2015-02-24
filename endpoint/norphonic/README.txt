                                                             Date: 2012-03-12
1. DESCRIPTION
  Brand package for configuring Norphonic Traphone with FreePBX
  Endpoint Manager module.
  Produced by Norphonic. Available on request at support@norphonic.com
  
2. INSTALLATION
2.1 FIRST
  [ ] Consult section 3, REQUIREMENTS.
2.2 INSTALLING ON FREEPBX 2.9
  [ ] Log in to your Freepbx system and go to [Tools] - [End Point
      Manager] - [End Point Advanced Settings].
  [ ] Click on [Manual Endpoint Modules Upload/Export].
  [ ] Click on [Browse] button next to the "Brand Package" field and locate
      the norphonic.tgz file. Click on [Upload].
  [ ] Go to [End Point Manager] - [End Point Configuration].
  [ ] Click on [Enable] on the "Norphonic Traphone".
  [ ] Go to [End Point Manager] - [End Point Device List].
  [ ] Enter the subnet where the phones will be found and click [go].
  [ ] Click the box to the left of the located phones and assign free
      directory numbers to the phones. Click [Add selected phones].
	  This will create the required configuration files to the TFTP download
	  area.
  [ ] Click on the phones [Edit] icon in [End Point Template Manager] to alter
      i.e. hotline and keypad option for the phones.

	  2.2 INSTALLING ON FREEPBX 2.10
  [ ] Log in to your Freepbx system and go to [Connectivity] - [End Point
      Advanced Settings].
  [ ] Click on [Manual Endpoint Modules Upload/Export].
  [ ] Click on [Browse] button next to the "Brand Package" field and locate
      the norphonic.tgz file. Click on [Upload].
  [ ] Go to [Connectivity] - [End Point Configuration].
  [ ] Click on [Enable] on the "Norphonic Traphone".
  [ ] Go to [Connectivity] - [End Point Device List].
  [ ] Enter the subnet where the phones will be found and click [go].
  [ ] Click the box to the left of the located phones and assign free
      directory numbers to the phones. Click [Add selected phones].
	  This will create the required configuration files to the TFTP download
	  area.
  [ ] Click on the phones [Edit] icon in [End Point Template Manager] to alter
      i.e. hotline and keypad option for the phones.
  
3. REQUIREMENTS
  a) Freepbx 2.9 or 2.10. Found at http://www.freepbx.org/
     Installed and set up with telephone number range ready for Traphone.
     As of March 1st, FreePBX 2.10 is still in Beta version.
	 	 
  b) Module Endpoint Manager 2.9.3.2 or 2.10.3.6 or newer found at 
     http://www.the159.com/endpointman/
     
  c) Your network must be set up with DHCP server to deliver IP address and
     TFTP server information to the Traphones at bootup. The DHCP TFTP option
	 must point to the FreePBX system. The Traphones may also be pre-configured
	 with static ip adresses.
  
4. RELEASE NOTES
    Be sure to read CHANGELOG.txt for important changes.
  a) Rapid spanning tree configuration can not be modified from Endpoint Manager
  b) Verified on Traphone Firmware 1202241500.
  c) Online version not including firmware. Package with firmware is provided
     on request at sales@norphonic.com
  d) Firmware is not correctly copied to tftpboot area as of Endpoint Manager
     release 2.10.3.6
 
 
                                          Author: Thord Matre, Norphonic AS
                                                               Fabrikkgaten 5
                                                               N-5059 Bergen
                                                               Norway