import time
from PyQt5 import QtGui
from PyQt5.QtCore import *
from PyQt5.QtGui import *
from PyQt5.QtWidgets import *
import sys
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
import webbrowser
from JarvisUI_5 import Ui_MainWindow
from langchain_ollama import OllamaLLM
from langchain_core.prompts import ChatPromptTemplate
from bs4 import BeautifulSoup

engine = pyttsx3.init('sapi5')
voices = engine.getProperty('voices')
engine.setProperty('voices', voices[0].id)

template = """
You are JARVIS (Just A Rather Very Intelligent System) from the Iron Man movies. You are an intelligent assistant expert in handling all types of tasks.

If you feel like the user is asking for link to a particular website your response should just include the name of the website followed the the word "website" (eg - Geeks for Geeks, website).
And if you feel that the user wanted to open a particular thing on youtube or maybe you think that the name is of a song your response should be the (details of what user wants to open on youtube in brief), youtube. Just this.

Here is the conversation history: {context}

I Said: {query}

Response: 
"""

model = OllamaLLM(model="llama3.1")
prompt = ChatPromptTemplate.from_template(template)
chain = prompt | model

def speak(audio):
    engine.say(audio)
    print(audio)
    engine.runAndWait()

def wish():
    hour = int(datetime.datetime.now().hour)
    strTime = datetime.datetime.now().strftime("%I:%M %p")

    if hour >= 0 and hour <= 12:
        speak(f"Good Morning sir, It's {strTime} ")
    elif hour > 12 and hour < 18:
        speak(f"Good Afternoon sir, It's {strTime} ")
    else:
        speak(f"Good Evening sir, It's {strTime} ")

class MainThread(QThread):
    def __init__(self):
        super(MainThread, self).__init__()
        self.running = True  # Flag to control the thread

    def run(self):
        self.taskExecution()

    def stop(self):
        self.running = False  # Set the flag to stop the thread

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

    # def aiProcess(self, query):
    #     client = Groq(api_key=os.environ.get("GROQ_API_KEY"))
    #     chat_completion = client.chat.completions.create(
    #         messages=[
    #             {
    #                 "role": "system", 
    #                 "content": "You are a virtual assistant named Jarvis. Respond with a very short and appropriate action or command based on the input.",
    #                 "role": "user",
    #                 "content": f"User said: {query}",
    #             }
    #         ],
    #         model="llama3-70b-8192"
    #     )
    #     output = chat_completion.choices[0].message.content
    #     list.append(output)
    #     return output

    def search_google(self, output):
        url = f"https://www.google.com/search?q={output}"
        
        # Send a GET request to Google
        headers = {
            "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36"
        }
        response = requests.get(url, headers=headers)
        
        # Parse the HTML content
        soup = BeautifulSoup(response.content, 'html.parser')
        
        # Find all search result divs
        search_results = soup.find_all('div', class_='yuRUbf')

        if search_results:
            # Get the first search result
            first_result = search_results[0]
            title = first_result.find('h3').text
            link = first_result.find('a')['href']

            print(f"Title: {title}")
            print(f"Link: {link}")
            
            # Open the first link in the web browser
            webbrowser.open(link)
        else:
            print("No results found.")

    def processOutput(self, output):
        context = ""
        result = chain.invoke({"context": context,"query": output})
        # Process the output to perform actions
        if result == output:
            return
        if "website" in result:
            result = result.split(",")
            self.search_google(result[0])
        
        elif "youtube" in result:
            result = result.split(",")
            kit.playonyt(result[0])
            speak(f"Playing {result[0]} on Youtube")
            speak("I didn't understand that.")

    def weatherReport(self):
        # Your existing weather report code
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
            print("Weather Stats for - {}  ||  {}".format(location.upper(), date_time))
            print("----------------------------------------------------------- ")

            x = "Current temperature is :{:.2f} degree C".format(temp_city)
            y = "Current weather description: " + weather_desc
            z = "Current Humidity: " + str(hmdt) + '%'
            w = "Current wind speed: " + str(wind_spd) + ' kmph'
            speak(x)
            speak(y)
            speak(z)
            speak(w)

    def taskExecution(self):
        wish()
        while self.running:  # Use the running flag to control the loop
            query = self.takeCommand()

            if query != "none":
                self.processOutput(query)

class Main(QMainWindow):
    def __init__(self):
        super().__init__()
        self.ui = Ui_MainWindow()  # Assuming you have a UI class
        self.ui.setupUi(self)
        self.startTask()
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
        
        self.startExecution = MainThread()
        self.startExecution.start()
        
        self.showTime()

    def closeEvent(self, event):
        self.startExecution.stop()  # Stop the thread
        self.startExecution.quit()  # Quit the thread
        self.startExecution.wait()  # Wait for the thread to finish
        event.accept()  # Accept the close event

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