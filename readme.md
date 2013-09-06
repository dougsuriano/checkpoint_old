#Checkpoint
##Software for management of Alleycat style bike races.
##Created by Doug Suriano & Matt Savoia

###About
Checkpoint is a web application that allows for timing and scoring of alleycat races. Design goals include:
+ Accurate timing/points tracking.
+ Fast simple designs. Especially important when users are not in areas with good cellular coverage.


###History
This is the third iteration of alleycat software written by Doug Suriano & Matt Savoia. The first version was used during the 2012 Beast of the East Pre-NACCC event in Philadelphia, PA. The second version was used during the 2012 CMWC in Chicago, IL. The version released here contains ~90% of the source code used in the 2013 Seattle NACCC. Race specific tweaks and hacks were removed.


###Components
Checkpoint has several components to it. In this repository:
+ Checkpoint API: A REST-ful type API that allows clients to C.R.U.D race/event data. Implemented in PHP.
+ Checkpoint Worker API: A stripped down API designed for checkpoint workers. This API only allows for a subset of the features the main API provides. Also Implemented in PHP.
+ Checkpoint web app. Web style interface hacked together using basic HTML/CSS/jQuery. This was interface used during the SEA-NACCCs.

In a separate repository (coming soon....) is an iPhone application written in Cocoa Touch. This app communicates with the Checkpoint Worker API. This was used during the 2013 Seattle NACCCs.



###Future Improvements
Below is a list of features/enhancements I would like to see :
+ Convert to Web App to use Twitter Bootstrap for all design.
+ Adding Unit Tests for all API functions
+ Installation script to aid users in installing the app without having to get super nerdy.
+ More race options