# Phing property bundle

Contains a tasks to load in a full complement of all the files in a directory. 
This allows phing to load in a set of files without being overly verbose or tedious to maintain.
For example, pointing at a /properties folder with 8 yml files will automatically 
load in all 8 files into properties (prefixed with their filename). 

## Use cases
Assume you have a project with a lot of properties and various stages, environements etc. But still 
want to automate this build process. 
You could put everything in a very large build.properties file. But using this task you can make it into 
a dir based structure. 

E.g the file system looks like this:

properties/
    dist/
        project.yml
        behat.yml
        githook.yml
    env/
        githook.yml
    stage/
        db.yml

## Using the tasks 
Before any of the tasks are available you'll need to load them in via 
the build.xml. 

```
    
    <includepath classpath="${project.basedir}/vendor/surangapg/phing-property-bundle/src" />
    <taskdef classname="PropertyDirWriteTask" name="property-dir-write" />
    <taskdef classname="PropertyDirLoadTask" name="property-dir" />

```
    
### Writing all the properties

This will write a full set of consolidated properties to the /properties 
dir. 

```
    <taskdef classname="PropertyDirLoadTask" name="property-dir-load" />
    <property-dir-load originDir="${project.basedir}/properties"
                   subLevels="dist,env,stage"
                   outputDir="${project.basedir}/properties"
                   order="project,dir,bin,behat"
                   override="true"
                   outputFull="true"
    />
```

### Writing reading in a properties dir 

Reads all the consolidated data from the new dir. 
```
    <property-dir propertyDir="${project.basedir}/properties"/>
```