mythtranscode -i /etv/rec/10201_20210410230000.ts -m --honorcutlist  --passthrough  -o ~/tmp/transcode.ts
# Menu-> editing -> cut to the end to create cut list then run the above emd
# You will have to substitute the correct path and file name. After completing the transcode
# you can overwrite the original file with the transcoded file (I would keep the original around for a while in case of problems). If you overwite the original file with the new one you need to rebuild the seektable.
# You can get a list of transcode options using