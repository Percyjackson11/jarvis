# -*- coding: utf-8 -*-

# Form implementation generated from reading ui file '.\JarvisUI-5.ui'
#
# Created by: PyQt5 UI code generator 5.15.2
#
# WARNING: Any manual changes made to this file will be lost when pyuic5 is
# run again.  Do not edit this file unless you know what you are doing.


from PyQt5 import QtCore, QtGui, QtWidgets


class Ui_MainWindow(object):
    def setupUi(self, MainWindow):
        MainWindow.setObjectName("MainWindow")
        MainWindow.resize(1920, 1080)
        self.centralwidget = QtWidgets.QWidget(MainWindow)
        self.centralwidget.setObjectName("centralwidget")
        self.label = QtWidgets.QLabel(self.centralwidget)
        self.label.setGeometry(QtCore.QRect(0, 0, 1920, 1080))
        self.label.setStyleSheet("background-color: rgb(0, 0, 0);")
        self.label.setText("")
        self.label.setObjectName("label")
        self.stark_gif = QtWidgets.QLabel(self.centralwidget)
        self.stark_gif.setGeometry(QtCore.QRect(40, 40, 281, 171))
        self.stark_gif.setText("")
        self.stark_gif.setPixmap(QtGui.QPixmap(".\\stark.gif"))
        self.stark_gif.setScaledContents(True)
        self.stark_gif.setObjectName("stark_gif")
        self.holder = QtWidgets.QLabel(self.centralwidget)
        self.holder.setGeometry(QtCore.QRect(10, 10, 331, 231))
        self.holder.setText("")
        self.holder.setPixmap(QtGui.QPixmap(".\\holder(1).jpg"))
        self.holder.setScaledContents(True)
        self.holder.setObjectName("holder")
        self.cpu_ram_monitor = QtWidgets.QLabel(self.centralwidget)
        self.cpu_ram_monitor.setGeometry(QtCore.QRect(1500, 40, 381, 171))
        self.cpu_ram_monitor.setText("")
        self.cpu_ram_monitor.setPixmap(QtGui.QPixmap(".\\cpu-ram monitor(1).png"))
        self.cpu_ram_monitor.setScaledContents(True)
        self.cpu_ram_monitor.setObjectName("cpu_ram_monitor")
        self.label_3 = QtWidgets.QLabel(self.centralwidget)
        self.label_3.setGeometry(QtCore.QRect(1490, 30, 401, 191))
        self.label_3.setStyleSheet("background-color: rgb(85, 255, 255);")
        self.label_3.setText("")
        self.label_3.setObjectName("label_3")
        self.cpu_precent = QtWidgets.QLabel(self.centralwidget)
        self.cpu_precent.setGeometry(QtCore.QRect(1560, 130, 71, 41))
        self.cpu_precent.setStyleSheet("background-color: rgb(30,30,30);\n"
"font: 10pt \"Eras Medium ITC\";\n"
"color: rgb(255, 255, 255);")
        self.cpu_precent.setText("")
        self.cpu_precent.setAlignment(QtCore.Qt.AlignCenter)
        self.cpu_precent.setObjectName("cpu_precent")
        self.ram_percent = QtWidgets.QLabel(self.centralwidget)
        self.ram_percent.setGeometry(QtCore.QRect(1740, 130, 91, 41))
        self.ram_percent.setStyleSheet("background-color: rgb(30,30,30);\n"
"font: 10pt \"Eras Medium ITC\";\n"
"color: rgb(255, 255, 255);")
        self.ram_percent.setText("")
        self.ram_percent.setAlignment(QtCore.Qt.AlignCenter)
        self.ram_percent.setObjectName("ram_percent")
        self.gif = QtWidgets.QLabel(self.centralwidget)
        self.gif.setGeometry(QtCore.QRect(240, 110, 1271, 851))
        self.gif.setText("")
        self.gif.setPixmap(QtGui.QPixmap(".\\main.gif"))
        self.gif.setScaledContents(True)
        self.gif.setObjectName("gif")
        self.suit_controls = QtWidgets.QLabel(self.centralwidget)
        self.suit_controls.setGeometry(QtCore.QRect(1560, 360, 321, 521))
        self.suit_controls.setText("")
        self.suit_controls.setPixmap(QtGui.QPixmap(".\\boundary(1).jpg"))
        self.suit_controls.setScaledContents(True)
        self.suit_controls.setObjectName("suit_controls")
        self.me = QtWidgets.QLabel(self.centralwidget)
        self.me.setGeometry(QtCore.QRect(640, 890, 71, 71))
        self.me.setText("")
        self.me.setPixmap(QtGui.QPixmap(".\\jarvis_logo_border.png"))
        self.me.setScaledContents(True)
        self.me.setObjectName("me")
        self.label_6 = QtWidgets.QLabel(self.centralwidget)
        self.label_6.setGeometry(QtCore.QRect(720, 850, 55, 16))
        self.label_6.setObjectName("label_6")
        self.label_2 = QtWidgets.QLabel(self.centralwidget)
        self.label_2.setGeometry(QtCore.QRect(710, 820, 61, 71))
        self.label_2.setText("")
        self.label_2.setPixmap(QtGui.QPixmap(".\\arrow(1).jpg"))
        self.label_2.setScaledContents(True)
        self.label_2.setObjectName("label_2")
        self.label_5 = QtWidgets.QLabel(self.centralwidget)
        self.label_5.setGeometry(QtCore.QRect(1010, 820, 61, 71))
        self.label_5.setText("")
        self.label_5.setPixmap(QtGui.QPixmap(".\\arrow(2).jpg"))
        self.label_5.setScaledContents(True)
        self.label_5.setObjectName("label_5")
        self.tony = QtWidgets.QLabel(self.centralwidget)
        self.tony.setGeometry(QtCore.QRect(1060, 890, 71, 71))
        self.tony.setText("")
        self.tony.setPixmap(QtGui.QPixmap(".\\buildschool.png"))
        self.tony.setScaledContents(True)
        self.tony.setObjectName("tony")
        self.label_7 = QtWidgets.QLabel(self.centralwidget)
        self.label_7.setGeometry(QtCore.QRect(1570, 430, 301, 5))
        self.label_7.setStyleSheet("background-color: rgb(0, 255, 255);")
        self.label_7.setText("")
        self.label_7.setObjectName("label_7")
        self.label_8 = QtWidgets.QLabel(self.centralwidget)
        self.label_8.setGeometry(QtCore.QRect(1590, 380, 261, 41))
        self.label_8.setStyleSheet("color: rgb(255, 255, 255);\n"
"font: 20pt \"Eras Medium ITC\";")
        self.label_8.setAlignment(QtCore.Qt.AlignCenter)
        self.label_8.setObjectName("label_8")
        self.label_9 = QtWidgets.QLabel(self.centralwidget)
        self.label_9.setGeometry(QtCore.QRect(1590, 450, 55, 16))
        self.label_9.setStyleSheet("font: 9pt \"Eras Medium ITC\";\n"
"color: rgb(255, 255, 255);")
        self.label_9.setObjectName("label_9")
        self.status = QtWidgets.QLabel(self.centralwidget)
        self.status.setGeometry(QtCore.QRect(1700, 450, 161, 16))
        self.status.setStyleSheet("background-color: rgb(255, 0, 0);\n"
"font: 8pt \"Eras Medium ITC\";\n"
"border: 2px Solid Black;")
        self.status.setAlignment(QtCore.Qt.AlignCenter)
        self.status.setObjectName("status")
        self.label_10 = QtWidgets.QLabel(self.centralwidget)
        self.label_10.setGeometry(QtCore.QRect(1590, 490, 131, 16))
        self.label_10.setStyleSheet("font: 9pt \"Eras Medium ITC\";\n"
"color: rgb(255, 255, 255);")
        self.label_10.setObjectName("label_10")
        self.wifi = QtWidgets.QPushButton(self.centralwidget)
        self.wifi.setGeometry(QtCore.QRect(1730, 480, 131, 31))
        self.wifi.setStyleSheet("background-color: transparent;\n"
"color: rgb(255, 255, 255);\n"
"font: 8pt \"Eras Medium ITC\";")
        self.wifi.setText("")
        icon = QtGui.QIcon()
        icon.addPixmap(QtGui.QPixmap(".\\wifi.png"), QtGui.QIcon.Normal, QtGui.QIcon.Off)
        self.wifi.setIcon(icon)
        self.wifi.setObjectName("wifi")
        self.connect = QtWidgets.QPushButton(self.centralwidget)
        self.connect.setGeometry(QtCore.QRect(1652, 540, 151, 28))
        self.connect.setStyleSheet("background-color: rgb(135, 255, 191);\n"
"border-radius:5px;")
        self.connect.setObjectName("connect")
        self.label_11 = QtWidgets.QLabel(self.centralwidget)
        self.label_11.setGeometry(QtCore.QRect(30, 640, 281, 191))
        self.label_11.setText("")
        self.label_11.setPixmap(QtGui.QPixmap(".\\boundary(1).jpg"))
        self.label_11.setScaledContents(True)
        self.label_11.setObjectName("label_11")
        self.play = QtWidgets.QPushButton(self.centralwidget)
        self.play.setGeometry(QtCore.QRect(140, 760, 61, 61))
        self.play.setStyleSheet("background-color: transparent;")
        self.play.setText("")
        icon1 = QtGui.QIcon()
        icon1.addPixmap(QtGui.QPixmap(".\\play.png"), QtGui.QIcon.Normal, QtGui.QIcon.Off)
        self.play.setIcon(icon1)
        self.play.setIconSize(QtCore.QSize(35, 35))
        self.play.setObjectName("play")
        self.previous = QtWidgets.QPushButton(self.centralwidget)
        self.previous.setGeometry(QtCore.QRect(60, 760, 61, 61))
        self.previous.setStyleSheet("background-color: transparent;")
        self.previous.setText("")
        icon2 = QtGui.QIcon()
        icon2.addPixmap(QtGui.QPixmap(".\\previous.png"), QtGui.QIcon.Normal, QtGui.QIcon.Off)
        self.previous.setIcon(icon2)
        self.previous.setIconSize(QtCore.QSize(35, 35))
        self.previous.setObjectName("previous")
        self.next = QtWidgets.QPushButton(self.centralwidget)
        self.next.setGeometry(QtCore.QRect(220, 760, 61, 61))
        self.next.setStyleSheet("background-color: transparent;")
        self.next.setText("")
        icon3 = QtGui.QIcon()
        icon3.addPixmap(QtGui.QPixmap(".\\next.png"), QtGui.QIcon.Normal, QtGui.QIcon.Off)
        self.next.setIcon(icon3)
        self.next.setIconSize(QtCore.QSize(35, 35))
        self.next.setObjectName("next")
        self.song_name = QtWidgets.QLabel(self.centralwidget)
        self.song_name.setGeometry(QtCore.QRect(74, 730, 191, 20))
        self.song_name.setStyleSheet("font: 9pt \"Eras Medium ITC\";\n"
"color: rgb(255, 255, 255);")
        self.song_name.setAlignment(QtCore.Qt.AlignCenter)
        self.song_name.setObjectName("song_name")
        self.time = QtWidgets.QTextBrowser(self.centralwidget)
        self.time.setGeometry(QtCore.QRect(50, 900, 151, 61))
        self.time.setStyleSheet("background-color: transparent;\n"
"color: rgb(255, 255, 255);\n"
"font: 12pt \"Eras Medium ITC\";")
        self.time.setObjectName("time")
        self.date = QtWidgets.QTextBrowser(self.centralwidget)
        self.date.setGeometry(QtCore.QRect(200, 900, 161, 61))
        self.date.setStyleSheet("background-color: transparent;\n"
"color: rgb(255, 255, 255);\n"
"font: 12pt \"Eras Medium ITC\";")
        self.date.setObjectName("date")
        self.pushButton = QtWidgets.QPushButton(self.centralwidget)
        self.pushButton.setGeometry(QtCore.QRect(1660, 240, 93, 28))
        self.pushButton.setStyleSheet("font: 9pt \"Eras Medium ITC\";\n"
"color: rgb(255, 255, 255);\n"
"border: 2px Solid Blue;\n"
"border-radius: 5px;\n"
"")
        self.pushButton.setObjectName("pushButton")
        self.label.raise_()
        self.gif.raise_()
        self.holder.raise_()
        self.stark_gif.raise_()
        self.label_3.raise_()
        self.cpu_ram_monitor.raise_()
        self.cpu_precent.raise_()
        self.ram_percent.raise_()
        self.suit_controls.raise_()
        self.me.raise_()
        self.label_6.raise_()
        self.label_2.raise_()
        self.label_5.raise_()
        self.tony.raise_()
        self.label_7.raise_()
        self.label_8.raise_()
        self.label_9.raise_()
        self.status.raise_()
        self.label_10.raise_()
        self.wifi.raise_()
        self.connect.raise_()
        self.label_11.raise_()
        self.play.raise_()
        self.previous.raise_()
        self.next.raise_()
        self.song_name.raise_()
        self.time.raise_()
        self.date.raise_()
        self.pushButton.raise_()
        MainWindow.setCentralWidget(self.centralwidget)

        self.retranslateUi(MainWindow)
        QtCore.QMetaObject.connectSlotsByName(MainWindow)

    def retranslateUi(self, MainWindow):
        _translate = QtCore.QCoreApplication.translate
        MainWindow.setWindowTitle(_translate("MainWindow", "MainWindow"))
        self.label_6.setText(_translate("MainWindow", "TextLabel"))
        self.label_8.setText(_translate("MainWindow", "SUIT"))
        self.label_9.setText(_translate("MainWindow", "STATUS:"))
        self.status.setText(_translate("MainWindow", "DISCONNECTED"))
        self.label_10.setText(_translate("MainWindow", "CONNECTED WIFI:"))
        self.connect.setText(_translate("MainWindow", "CONNECT TO SUIT"))
        self.song_name.setText(_translate("MainWindow", "No Song Playing Currently..."))
        self.pushButton.setText(_translate("MainWindow", "Refresh data"))


if __name__ == "__main__":
    import sys
    app = QtWidgets.QApplication(sys.argv)
    MainWindow = QtWidgets.QMainWindow()
    ui = Ui_MainWindow()
    ui.setupUi(MainWindow)
    MainWindow.show()
    sys.exit(app.exec_())
