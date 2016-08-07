# File Preview Generator Bundle #

This bundle provides classes to generate preview image for office files like .doc, .docx, .xls and others. 
It contains client and server that let use the bundle both locally and remotely.

## System requiremnts (for Microsoft Windows)

A) Microsoft Word must be installed. Also you need to configure COM-objects
From the Start menu, click Run and type Dcomcnfg.exe.
In Component Services, click Console root, expand Component Services, expand Computers, then My Computer, then DCOM Configuration
Find entity named Microsoft Word 97-2003 Document. Right click on it, then Properties.
In the tab Identity choose This user and enter correct admin login and password. Save changes.

B) Install GhostScript

C) Install ImageMagic
All *_.dll file in folder `modules/coders` inside your `ImageMagic directory` copy to `ImageMagic directory` and `C:\Windows\System32` and `C:\Windows\SysWOW64`
All *_.dll file in folder `modules/filters` inside your `ImageMagic directory` copy to `ImageMagic directory` `C:\Windows\System32` and `C:\Windows\SysWOW64`

D) Find and download archive php_imagick-3.4.1-7.0-ts-vc14-x64

Copy all CORE.dll's from it to `C:\Windows\System32` and `C:\Windows\SysWOW64`

Copy php_imagick.dll to `ext` folder in your `php directory`

E) Add MAGICK_HOME to your environment PATH

F) Edit php.ini file in your `php` directory 

Uncomment or add next lines in Windows extensions section:

    extension=php_curl.dll
    extension=php_fileinfo.dll
    extension=php_gd2.dll
    extension=php_mbstring.dll
    extension=php_exif.dll
    extension=php_openssl.dll
    extension=php_com_dotnet.dll
    extension=php_imagick.dll

	;...
	
    memory_limit = 1024M

G) Reboot (here it's really important!)

## Installation

### With composer

This bundle can be installed using [composer](https://getcomposer.org/):

    composer require garant/file-preview-generator-bundle
    
### Register the bundle

    <?php
    
    // app/AppKernel.php
    
    public function registerBundles()
    {
        $bundles = array(
    
            // ...
            new Liip\ImagineBundle\LiipImagineBundle(),
            // ...
            new Garant\GarantFilePreviewGeneratorBundle\GarantFilePreviewGeneratorBundle(),
        );
    
    	// ...
    }

### Configuration

You can add remote servers:

    garant_file_preview_generator:
        #
        # Available algorithms: random, round_robin
        #server_select_algorithm: random
        #
        # Remote servers are used to generate file preview
        # To start generator server use "garant:file-preview-generator:server-start <server_name>" command
        #servers:
        #    local_unix:
        #        host: 127.0.0.1
        #        port: 9010
        #
        #    remote_windows:
        #        host: 192.168.10.201
        #        port: 9010
        #    
        #    remote_windows_secure:
        #        protocol: https 
        #        host: 192.168.10.201
        #        port: 9011
        
Optionally you can configure Liip imagine filters to post process preview images:

    liip_imagine:
        filter_sets:
            avatar_square:
                filters:
                    # Transforms 150x140 to 120x120, while cropping the width
                    thumbnail: { size: [120, 120], mode: outbound }

### Console commands

If remote server is configured you can start it. Call this command on remote server:

    bin/console garant:file-preview-generator:server-start <server_name>
    
Also you can start server with -vvv flag to check memory usage and get other information about conversion process.
  
### Services

To generate preview you can use generator service. Available services:

    garant_file_preview_generator.remote_client - Remote client. 
    garant_file_preview_generator.libreoffice_generator - Local generator based on LibreOffice
    garant_file_preview_generator.msoffice_generator - Local generator based on MS Office and COM 

### Example

    $temp_file = new \SplFileObject('test.docx');

    $generator = $this->container->get('garant_file_preview_generator.libreoffice_generator');
    $temp_preview_file = $generator->generate($temp_file);
    if(!$temp_preview_file){
        $this->get('logger')->err('Preview attachment: Preview generation error');
        throw new \RuntimeException('Preview generation error');
    }
    
    dump($temp_preview_file);
    
    // Generate filtered preview
	$generator->setFilter('avatar_square');
    $temp_preview_file = $generator->generate($temp_file);
    
	dump($temp_preview_file);
	
	// Generate preview in PDF or other format
	// see AbstractGenerator class constants
	$generator->setOutFormat(AbstractGenerator::PREVIEW_FORMAT_PDF);
    $temp_preview_file = $generator->generate($temp_file);
    
	dump($temp_preview_file);
	
