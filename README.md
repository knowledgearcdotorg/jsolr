# JSolr

Apache Solr Server-based indexing and search for Joomla.

## Full Text Indexing

JSolr uses Apache Tika for any full text extraction. Access to both the Tika App and Tika Server is provided via JSolr's API.

The Apache Tika App has been tested with version 1.8 and should work with any newer release.

The Apache Tika Server has been tested with version 1.9. Releases from 1.10 to 1.13 will not work with JSolr do to the removal of the remote file extraction functionality although this feature has been re-introduced into 1.14+ provided Tika Server is run with the -enableUnsecureFeatures and -enableFileUrl flags.