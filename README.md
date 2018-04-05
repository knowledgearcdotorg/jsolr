# JSolr

Apache Solr Server-based indexing and search for Joomla.

The current version of JSolr is optimized for the latest version of Apache Solr, which, at this time is 7.2.1. However, you can run JSolr against version 6.x with some changes to the Solr schema. Configuration for information about how to configure Apache Solr 6.x is included in this readme and also in the official JSolr documentation.

## Installation

Use the Joomla! Extension Manager to install the pk_jsolr.zip package. The package includes:

- The JSolr component
- The JSolr library (including Solarium)
- The filter module
- The latest indexed items module
- The content events bridge plugin (which translates calls to the onContent events into the onJSolr equivalents)

You can also using the phing script to zip the package yourself using the phing target "package".

### Building from Source

To build the package from source:

- Download the code from Github,
- Copy build.properties.example to build.properties,
- Open the build.properties file and configure as necessary,
- Run phing help for a list of available targets,
- Run phing package to create the installable JSolr package.

## Configuration

There is no Solr schema included with JSolr. Instead, JSolr uses Apache Solr's schemaless architecture.

JSolr is configured to work seamlessly with Apache Solr 7.x and up. However, JSolr will work with the older 6.x version with a some minor changes to the managed schema. In particular, the single date values need to match newer versions. To support Apache Solr 6.x add the following fields:  

***_dt**

- Type: pdate
- Indexed: true
- Stored: true

***_dts**

- Type: pdate
- Indexed: true
- Stored: true
- MultiValued: true

When indexing language-specific content, JSolr uses Joomla's language manager. Some of Joomla's 2 letter ISO language codes differ to Solr's so you will need to accommodate for these differences in your Apache Solr configuration. For example, the Norwegian two letter code nb is used in Joomla but in Apache Solr it is no. To address this, you will need to add a dynamic field for the Joomla version of the language code, E.g.  

***_txt_nb**

- Type: text_no
- Indexed: true
- stored: true

## Full Text Indexing

JSolr uses Apache Tika for any full text extraction. Access to both the Tika App and Tika Server is provided via JSolr's API.

The Apache Tika App has been tested with version 1.8 and should work with any newer release.

The Apache Tika Server has been tested with version 1.9. Releases from 1.10 to 1.13 will not work with JSolr do to the removal of the remote file extraction functionality although this feature has been re-introduced into 1.14+ provided Tika Server is run with the -enableUnsecureFeatures and -enableFileUrl flags.