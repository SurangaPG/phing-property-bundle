# Phing property bundle

Contains a tasks to load in a full complement of all the files in a directory. 
This allows phing to load in a set of files without being overly verbose or tedious to maintain.
For example, pointing at a /config folder with 8 yml files will automatically 
load in all 8 files into properties (prefixed with their filename). 
