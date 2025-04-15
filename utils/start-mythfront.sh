#/usr/bin/bash

# mythfrontend --override-setting UIPainter=opengl2 -v playback,channel
# mythfrontend -v gpu --loglevel=debug

# mythfrontend --override-setting UIPainter=qt -platform xcb -v playback,channel

export QT_QPA_PLATFORM=xcb
mythfrontend

