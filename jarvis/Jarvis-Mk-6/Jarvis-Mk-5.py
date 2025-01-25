import time
from PyQt5 import QtGui
from PyQt5.QtCore import *
from PyQt5.QtGui import *
from PyQt5.QtGui import QMovie
from PyQt5.QtWidgets import *
import sys
from JarvisUI_5 import Ui_MainWindow
import pyttsx3
import speech_recognition as sr
import requests
import pywhatkit as kit
import psutil
import subprocess
import random
import os
import pygame
import pyautogui
import datetime
import wikipedia
import wolframalpha
from groq import Groq


list = [""]

engine = pyttsx3.init('sapi5')
voices = engine.getProperty('voices')
engine.setProperty('voices',voices[0].id)

def speak(audio):
    engine.say(audio)
    print(audio)
    engine.runAndWait()

def wish():
    hour = int(datetime.datetime.now().hour)
    strTime = datetime.datetime.now().strftime("%I:%M %p")

    if hour>=0 and hour<=12:
        speak(f"Good Morning sir, Its {strTime} ")
                
    elif hour>12 and hour<18:
        speak(f"Good Afternoon sir, Its {strTime} ")
    
    else:
        speak(f"Good Evening sir, Its {strTime} ")
        
def weatherReport():
    ipAdd = requests.get('https://api.ipify.org').text
    url = 'https://get.geojs.io/v1/ip/geo/'+ipAdd+'.json'
    geo_requests = requests.get(url)
    geo_data = geo_requests.json()
    city = geo_data['city']

    user_api = ('294bc099cbca8f73ec6d6c7ccc8b8818')
    location = city
    link = "https://api.openweathermap.org/data/2.5/weather?q="+location+"&appid="+user_api

    api_link = requests.get(link)
    api_data = api_link.json()

    if api_data['cod'] == '404':
        speak("Invalid city")
        print("Invalid city: {}, Please check your city name".format(location))
    else:
        temp_city = ((api_data['main']['temp']) - 273.15)
        weather_desc = api_data['weather'][0]['description']
        hmdt = api_data['wind']['speed']
        wind_spd = api_data['wind']['speed']
        date_time = datetime.datetime.now().strftime("%d %b %Y | %I:%M:%S %p")

        print("-----------------------------------------------------------")
        print("Weather Stats for - {}  ||  {}".format(location.upper(),date_time))
        print("----------------------------------------------------------- ")

        x = "Current temperature is :{:.2f} degree C".format(temp_city)
        y = "Current weather description :",weather_desc
        z = "Current Humidity :",hmdt, '%'
        w = "Current wind speed :",wind_spd , 'kmph'
        speak(x)
        speak(y)
        speak(z)
        speak(w)

class MainThread(QThread):
    def __init__(self):
        super(MainThread, self).__init__()

    def run(self):
        self.taskExecution()

    def takeCommand(self):
        try:
            r = sr.Recognizer()
            with sr.Microphone() as mic:
                print("Listening...")
                r.pause_threshold = 1
                audio = r.listen(mic, timeout=2, phrase_time_limit=5)
                print("Recognizing...")
                query = r.recognize_google(audio, language='en-in')
                query = query.lower()
                print(f"User said: {query}")

        except Exception as e:
            print("Say that again please...")
            return "none"

        return query
    
    def aiProcess(self):
        client = Groq(
            api_key=os.environ.get("GROQ_API_KEY"),
        )
        chat_completion = client.chat.completions.create(
            messages=[
                {
                    "role": "system", "content": "You are a virtual assistant named jarvis skilled in general tasks like Alexa and Google Cloud. Give short responses please",
                    "role": "user",
                    "content": f"This is the list of last 10 messages, respond according to flow of conversation: {list[-10:]}",
                }
            ],
            model="llama3-70b-8192"
        )
        output = chat_completion.choices[0].message.content
        list.append(output)
        return output

    def taskExecution(self):
        wish()
        while True:
            query = self.takeCommand()

            if "video call" in query:
                if "jarvis" in query:
                    query2 = query.replace("jarvis", "")
                    query1 = query2.replace("video", "")
                    search = query1.replace("call", "")
                else:
                    query1 = query.replace("video", "")
                    search = query1.replace("call", "")
                os.startfile("C:\\Program Files\\WindowsApps\\5319275A.WhatsAppDesktop_2.2212.8.0_x64__cv1g1gvanyjgm\\app\\Whatsapp")

                time.sleep(1)
                pyautogui.click(x=100, y=135)
                time.sleep(1)
                pyautogui.write(search)
                time.sleep(1)
                pyautogui.press('enter')
                time.sleep(1)
                pyautogui.click(x=1660, y=75)
                speak(f"Calling {search}")
            
            elif "call" in query:
                if "jarvis" in query:
                    query2 = query.replace("jarvis", "")
                    query1 = query2.replace("video", "")
                    search = query1.replace("call", "")
                
                else:
                    query1 = query.replace("video", "")
                    search = query1.replace("call", "")
                os.startfile("C:\\Program Files\\WindowsApps\\5319275A.WhatsAppDesktop_2.2212.8.0_x64__cv1g1gvanyjgm\\app\\Whatsapp")

                time.sleep(1)
                pyautogui.click(x=100, y=135)
                time.sleep(1)
                pyautogui.write(search)
                time.sleep(1)
                pyautogui.press('enter')
                time.sleep(1)
                pyautogui.click(x=1730, y=75)
                speak(f"Calling {search}")

            elif "what" in query or "who" in query or "how" in query or "when" in query or "which" in query:
                if "1" in query or "2" in query or "3" in query or "4" in query or "5" in query or "6" in query or "7" in query or "8" in query or "9" in query or "0" in query:
                    app_id = 'VWV5TA-6U7RHLT42P'
                    client = wolframalpha.Client(app_id)
                    res = client.query(query) 
                    answer = next(res.results).text
                    speak(answer)

                else:
                    try:
                        speak("Wait sir, searching...")
                        app_id = 'VWV5TA-6U7RHLT42P'
                        client = wolframalpha.Client(app_id)
                        res = client.query(query) 
                        answer = next(res.results).text
                        speak(answer)

                    except:
                        results = wikipedia.summary(query, sentences=1)
                        speak("According to wikipedia")
                        speak(results)

            elif "happy birthday" in query:
                speak("Okay sure")
                os.startfile("C:\\Users\\siddh\\OneDrive\\Documents\\J.A.R.V.I.S\\Jarvis-Mk-5\\happy birthday.py")
                time.sleep(1)
                pygame.mixer.init()
                pygame.mixer.music.load("C:\\Users\\siddh\\OneDrive\\Documents\\J.A.R.V.I.S\\Jarvis-Mk-5\\happy-birthday-mp3.mp3")
                pygame.mixer.music.play()
                speak("Happy Birthday Khushi")

            elif "weather report" in query:
                ipAdd = requests.get('https://api.ipify.org').text
                url = 'https://get.geojs.io/v1/ip/geo/'+ipAdd+'.json'
                geo_requests = requests.get(url)
                geo_data = geo_requests.json()
                city = geo_data['city']
                speak("Checking our location")
                speak("Fetching weather details")

                user_api = ('294bc099cbca8f73ec6d6c7ccc8b8818')
                location = city
                link = "https://api.openweathermap.org/data/2.5/weather?q="+location+"&appid="+user_api

                api_link = requests.get(link)
                api_data = api_link.json()

                if api_data['cod'] == '404':
                    speak("Invalid city")
                    print("Invalid city: {}, Please check your city name".format(location))
                else:
                    temp_city = ((api_data['main']['temp']) - 273.15)
                    weather_desc = api_data['weather'][0]['description']
                    hmdt = api_data['wind']['speed']
                    wind_spd = api_data['wind']['speed']
                    date_time = datetime.datetime.now().strftime("%d %b %Y | %I:%M:%S %p")

                    print("-----------------------------------------------------------")
                    print("Weather Stats for - {}  ||  {}".format(location.upper(),date_time))
                    print("----------------------------------------------------------- ")

                    x = "Current temperature is :{:.2f} degree C".format(temp_city)
                    y = "Current weather description :",weather_desc
                    z = "Current Humidity :",hmdt, '%'
                    w = "Current wind speed :",wind_spd , 'kmph'
                    speak(x)
                    speak(y)
                    speak(z)
                    speak(w)

            else:
                # Let OpenAI handle the request
                output = self.aiProcess()
                speak(output)


startExecution = MainThread()

class Main(QMainWindow):
    def __init__(self):
        super().__init__()
        self.ui = Ui_MainWindow()
        self.ui.setupUi(self)
        self.startTask()
        self.computer_info()
        self.ui.pushButton.clicked.connect(self.computer_info)
        self.ui.play.clicked.connect(self.play_song)
        self.ui.next.clicked.connect(self.next_song)
        self.ui.connect.clicked.connect(self.connect_to_suit)

    def startTask(self):
        self.ui.movie = QtGui.QMovie("main.gif")
        self.ui.gif.setMovie(self.ui.movie)
        self.ui.movie.start()
        self.ui.movie = QtGui.QMovie("suit_up.gif")
        self.ui.stark_gif.setMovie(self.ui.movie)
        self.ui.movie.start()
        timer = QTimer(self)
        timer.timeout.connect(self.showTime)
        timer.start(1000)
        startExecution.start()
        self.showTime()

    def computer_info(self):
        cpu = str(psutil.cpu_percent(4))
        ram = str(psutil.virtual_memory()[2])
        self.ui.cpu_precent.setText("")
        self.ui.cpu_precent.setText(cpu)
        self.ui.ram_percent.setText("")
        self.ui.ram_percent.setText(ram)

        wifi = subprocess.check_output(['netsh', 'WLAN', 'show', 'interfaces'])
        data = wifi.decode('utf-8')
        if "Rathod's" in data:
            self.ui.wifi.setText("Rathod's")

        elif "Sarla" in data:
            self.ui.wifi.setText("Sarla")

        elif "Galaxy A50sF418" in data:
            self.ui.wifi.setText("Galaxy A50sF418")

        else:
            self.ui.wifi.setText("Unknown Network or Not Connected")
            self.ui.wifi.setIcon(QtGui.QIcon("wifi-not-connected.png"))
    
    def next_song(self):
        pygame.mixer.init()
        song_path = "C:\\Users\\siddh\\OneDrive\\Documents\\Songs"
        song = os.listdir(song_path)
        play = random.choice(song)
        play1 = play.replace(".mp3", "")
        pygame.mixer.music.load(song_path + f"\\{play}")
        pygame.mixer.music.play()
        self.ui.play.setIcon(QtGui.QIcon("pause.png"))
        self.ui.song_name.setText(play1)

    def play_song(self):
        pygame.mixer.init()
        song_path = "C:\\Users\\siddh\\OneDrive\\Documents\\Songs"
        song = os.listdir(song_path)
        play = random.choice(song)
        if self.ui.song_name.text() == "No Song Playing Currently...":
            pygame.mixer.music.load(song_path + f"\\{play}")
            pygame.mixer.music.play()
            self.ui.play.setIcon(QtGui.QIcon("pause.png"))
            play1 = play.replace(".mp3", "")
            self.ui.song_name.setText(play1)
             
        else:
            pygame.mixer.music.load(song_path + f"\\{play}")
            pygame.mixer.music.pause()
            self.ui.play.setIcon(QtGui.QIcon("play.png"))
            self.ui.song_name.setText("No Song Playing Currently...")

    def connect_to_suit(self):
        pyautogui.press('win')
        time.sleep(1)
        pyautogui.write('cmd')
        pyautogui.press('enter')
        time.sleep(1)
        pyautogui.write('ssh -l pi 127.0.0.1 -p 30000')
        pyautogui.press('enter')
        time.sleep(1)
        pyautogui.write('siddhandvanshrpi32005')
        pyautogui.press('enter')
        self.ui.status.setText("Connected")
        self.ui.status.setStyleSheet("background-color: rgb(0, 255, 0);\n"
"font: 8pt \"Eras Medium ITC\";\n"
"border: 2px Solid Black;")



            
    def showTime(self):
        current_time = QTime.currentTime()
        current_date = QDate.currentDate()
        label_time = current_time.toString('hh:mm:ss')
        label_date = current_date.toString(Qt.ISODate)
        self.ui.date.setText(label_date)
        self.ui.time.setText(label_time)
        
        
app = QApplication(sys.argv)
jarvis = Main()
jarvis.show()
exit(app.exec_())