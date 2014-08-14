CUL-Activity-Stream
===================
Description
The feature set includes the following plugins:
Message Output
Block
Local Plugin
Core Code Hacks

Message Output Plugin
This plugin is a message processor that stores all sent messages in table {message_culactivity_stream}.

Block
The block selects and displays a list of the users messages from the table {message_culactivity_stream}. Each message includes a link to view the notification subject and one to remove the message form the users feed. With JS enabled, the block checks for new notifications every 5 mins. If the block is displayed on the site page then all course messages are included. If the block is displayed on a course page then only course messages for the containing course are displayed.

Local Plugin
This plugin catches course content creation and update events (\core\event\course_module_created, \core\event\course_module_updated) and sends new messages about the events. It does this by queueing a master message for each event in table {message_culactivity_stream_q} in real time. A cron job then runs every 5 mins and loops through unsent messages in the queue.  It sends individual messages to users and updates the status of the queued master message.

Core Code Hacks 
The block identifies course messages by displaying a course image or avatar (if the course has no course image). It also includes different messages depending on whether it is on a site or a course page. The messages are required to include the course id for this to work.  The event data sent to the message lib does not include the course id or any useful/consistent way of identifying the course that the message originated from. The core code hack adds the course id to the messages we care about. These are marked with a * in the table below. 


