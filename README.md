# moodle-jupyterhub-plugin
Jupyterhub plugin for Moodle

This repository contains 2 plugins made for Moodle 4.0
Those plugins are designed to make the communication from Moodle to a JupyterHub installation possible, allowing Moodle users to access their Jupyter files from Moodle - and more.

## assignsubmission plugin

This plugin adds a new type of Assignments: Jupyter notebooks.
- Teachers can select the assignment's material (notebooks, data, images, etc.) from their own Jupyter workspace
- Students can upload the assignment's material to their own Jupyter workspace, and submit their work

## assignfeedback plugin

This plugin is still under development ; the current version allows teachers to download all students' submissions in one click into the teacher's Jupyter workspace.

# Installation

Plugins' content need to be copied over to:
```
[moodle_root]/mod/assign/submission/noto
```
and
```
[moodle_root]/mod/assign/feedback/noto
```
respectively, on the Moodle server.

# Connection to File Server API

On the JupyterHub side, an API needs to be deployed on a server that has access to all user's files - typically the file server of the JupyterHub installation.
In Kubernetes, the API is deployed in the same namespace as the JupyterHub.

See this repository for the API: [epfl-cede/jupyterhub-fileserver-api](https://github.com/epfl-cede/jupyterhub-fileserver-api)

# Configuration
Go to _Site administration_ and set some global options first:

## Kubernetes
Select the option _ETHZ Installation_ to switch to the settings suitable for Kubernetes.

### API URL
At ETHZ, we run one JupyterHub per Moodle course, isolated in different namespaces. The key for the
different instances is the Moodle course ID. The configuration field allows to use a place holder 
for the Moodle course ID:

```
https://your-api-url-base-[courseid].example.com
```

### API Username and Secret Key
Authentication to the API is secured by a shared key/secret pair. Python 3.6+ example to 
create a key or secret:
```
import secrets
secrets.token_hex(32)
```
Use the same credentials to configure the `AUTH_USER` and `AUTH_KEY` variables for the API
deployment.


### API Username Parameters
Set to _idnumber_ and leave the prefix empty.


## Activity Settings
To configure an assignment activity, your Jupyter Hub home directory must already exists. In
Kubernetes, it gets created upon your first login into Jupyter. 

In the _Submission types_ section, activate the _Jupyter notebooks_ option. The contents of
your home directory will be displayed. Browse your directory tree and select the source
folder of your assignment.

__Note__: the path to the source folder is immutable and cannot be changed later.

Students assigned to the activity will see a similar dialog where they can select a folder
in their own home directory to fetch a copy of your source.
