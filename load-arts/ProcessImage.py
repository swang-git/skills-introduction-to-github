import os
import urllib.request

class Img:
    def __init__(self, tag, ymd, qid):
        self.tag = tag
        self.ymd = ymd
        self.qid = qid
        self.year = str(ymd)[0:4]
    def sav(self, lnk):
        imgsrc = "/daily_data/" + self.tag + "/" + str(self.year) + "/" + str(self.ymd) + "/images/" + str(self.qid) + "_" + os.path.basename(lnk)
        if self.qid == 'testing':
            tofile = "/home/swang/tmp/" + str(self.qid) + "_"  + os.path.basename(lnk)
        else:
            tofile = "/home/swang/Sites/webdata" + imgsrc

        if os.path.isfile(tofile):
            dout('IMG EXI', tofile, 'exists, skip')
        else:
            dout('saveImg', lnk, 'to:', tofile)
            urllib.request.urlretrieve(lnk, tofile)

        self.img_txt = '<img src="' + imgsrc + '" style="max-width:500">'
        return self.img_txt

    def getImgtxt():return self.img_txt
# resource = urllib.urlopen("http://www.digimouth.com/news/media/2011/09/google-logo.jpg")
# output = open("file01.jpg","wb")
# output.write(resource.read())
# output.close()
