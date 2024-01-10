Script to create a Joomla! extension installation zip file from a PhpStorm project.

The following project folders are not included the zip file ...
* .git
* .idea
* _

The following project files are not included in the zip file ...
* .doc
* .md

.doc files are used to generate .pdf files which form part of the extension.<br/>
.pdf files not normally included in github and the .doc is the master.

Configure in PHP storm _Settings / Tools / External Tools_ as shown below.

![](./images/mkzip.jpg)

For package extensions you can include references to other repositories in a
file called 'extensions.txt'. This avoids the need to duplicate the code in
two or or repositories.

The directory tree will look like this;
<pre>
Packages/pkg_mypackage
Packages/pkg_mypackage/mypackage.xml
Packages/pkg_mypackage/extensions/myextension...etc...
Packages/pkg_mypackage/extensions.txt
</pre>
The content of _Packages/mypackage/extensions.txt_ for a package
which includes 4 components might look something like this:
<pre>
# Comments can be included like this...
# These are standalone components
Components/com_somecomponent1
Components/com_somecomponent2
# These components are included in another package
Packages/pkg__somepackage/extensions/com_somecomponent3
Packages/pkg__somepackage/extensions/com_somecomponent4
</pre>
