Dinkly Change Log
=================

11.16.17 (v3.24): Updated resetContext to accept URI string

10.24.17 (v3.23): Fixed a routing bug that presented itself when zeros were passed as parameter values.

10.05.17 (v3.22): Updated DinklyDataTables classes to utilize late static binding and subsequently overrides.

09.27.17 (v3.21): Added fix to supress array notices when using loadComponent without parameters.

08.23.17 (v3.20): Added filterFiles(), hasFile(), fetchFiles(), and fetchFile(). 

08.23.17 (v3.19): Marked hasParameter() as deprecated. Added hasGetParam(), hasPostParam(), fetchGetParam(), fetchPostParam().

08.14.17 (v3.18): Cleaned up some left-over debug code. Fixed an issue that was preventing dinkly from handling unfulfilled favicon.ico requests properly, causing some unfortunate repercussions to database connections.

08.08.17 (v3.17): DinklyDataTables learns a new trick: custom labels without the need for a count.

08.07.17 (v3.16): Improved handling of component parameters. Components can now be safely nested, and any desired parameters can be passed into the loadModule() function to make accesible in the view.

06.29.17 (v3.15): Change log is introduced. The module-accessible `parameters` array has been refined into three separate entities. Parameters can still be passed around as always, and this update is backwards-compatible with legacy code. However, going forward, use of `$this->fetchGetParams()` and `$this->fetchPostParams()` will be encouraged inside of controller contexts. The parameters that are passed around via `loadModule()` calls are now referred to as `module_params`. Each of these different parameter types also has a filter function that can be overridden in the main Dinkly class as needed to handle any desired post-processing.