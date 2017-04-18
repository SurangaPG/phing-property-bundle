# Phing property bundle

Contains a tasks to load in a full complement of all the files in a directory. 
This allows phing to load in a set of files without being overly verbose or tedious to maintain.
For example, pointing at a /config folder with 8 yml files will automatically 
load in all 8 files into properties (prefixed with their filename). 

You can include it via the xml adding this (assuming the repository is checked out in the mentioned directory). 

```
    <includepath classpath="${project.basedir}/phing/property-bundle/src" />
    <taskdef classname="PropertyDirLoadTask" name="property-dir-load" />
    <property-dir-load originDir="${project.basedir}/config/src"
                   subLevels="dist,local,env"
                   outputDir="${project.basedir}/config"
                   order="project,dir,bin,behat"
                   override="true"
                   outputFull="true"
    />
```