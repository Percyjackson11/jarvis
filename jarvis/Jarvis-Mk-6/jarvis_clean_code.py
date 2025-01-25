# pip install setuptools
import time
import sys
import os
import random
import requests
import pygame
import pyttsx3
import speech_recognition as sr
import psutil
import subprocess
import pyautogui
import datetime
from PyQt5 import QtGui
from PyQt5.QtCore import *
from PyQt5.QtGui import QMovie
from PyQt5.QtWidgets import *
from JarvisUI_5 import Ui_MainWindow
from langchain_ollama import OllamaLLM
from langchain_core.prompts import ChatPromptTemplate
from bs4 import BeautifulSoup
import webbrowser
import pywhatkit as kit

# Initialize text-to-speech engine
engine = pyttsx3.init('sapi5')
voices = engine.getProperty('voices')
engine.setProperty('voices', voices[0].id)

template = """
You are JARVIS (Just A Rather Very Intelligent System) from the Iron Man movies. You are an intelligent assistant specifically designed to handle all types of tasks without deviation.

Ignore the 'User:' and 'Jarvis:' prompts; they are simply for maintaining the flow and context of the conversation. Do not refer back to previous conversations unless explicitly instructed to do so.

When a user requests a link to a particular website, respond in the following format: [one-line detail about the website, or just the name if no details are available] | Name of website | "website".

If the user mentions a song or requests to play something on YouTube, respond in the following format: Name of the song | youtube.

For all other inquiries, respond in a manner consistent with JARVIS's character—intelligent, efficient, and helpful.

Here is the conversation history: {context}

I Said: {query}

Response: 
"""
model = OllamaLLM(model="llama3.1")
prompt = ChatPromptTemplate.from_template(template)
chain = prompt | model

def speak(audio):
    """Convert text to speech."""
    engine.say(audio)
    print(audio)
    engine.runAndWait()

def wish():
    """Greet the user based on the time of day."""
    hour = int(datetime.datetime.now().hour)
    strTime = datetime.datetime.now().strftime("%I:%M %p")
    
    if hour < 12:
        speak(f"Good Morning sir, It's {strTime}")
    elif hour < 18:
        speak(f"Good Afternoon sir, It's {strTime}")
    else:
        speak(f"Good Evening sir, It's {strTime}")

def weatherReport():
    """Fetch and report the current weather."""
    ipAdd = requests.get('https://api.ipify.org').text
    url = f'https://get.geojs.io/v1/ip/geo/{ipAdd}.json'
    geo_requests = requests.get(url)
    geo_data = geo_requests.json()
    city = geo_data['city']

    user_api = '294bc099cbca8f73ec6d6c7ccc8b8818'  # Replace with your actual API key
    link = f"https://api.openweathermap.org/data/2.5/weather?q={city}&appid={user_api}"
    api_data = requests.get(link).json()

    if api_data['cod'] == '404':
        speak("Invalid city")
        print(f"Invalid city: {city}, Please check your city name")
    else:
        temp_city = api_data['main']['temp'] - 273.15
        weather_desc = api_data['weather'][0]['description']
        wind_spd = api_data['wind']['speed']
        date_time = datetime.datetime.now().strftime("%d %b %Y | %I:%M:%S %p")

        # Log weather stats
        print("-----------------------------------------------------------")
        print(f"Weather Stats for - {city.upper()} || {date_time}")
        print("-----------------------------------------------------------")

        # Prepare weather report
        speak(f"Current temperature is: {temp_city:.2f} degree C")
        speak(f"Current weather description: {weather_desc}")
        speak(f"Current wind speed: {wind_spd} km/h")

class MainThread(QThread):
    def __init__(self):
        super(MainThread, self).__init__()
        self.context = ""

    def run(self):
        """Start executing tasks."""
        wish()
        while True:
            self.wakeWord()

    def wakeWord(self):
        #Will call the takeCommand function only when it hears jarvis
        try:
            r = sr.Recognizer()
            with sr.Microphone() as mic:
                print("Listening for wakeword...")
                r.pause_threshold = 1
                audio = r.listen(mic, timeout=2, phrase_time_limit=4)
                query = r.recognize_google(audio, language='en-in').lower()
                print(f"User said: {query}")
                pygame.mixer.init() 
                pygame.mixer.music.load("wake_word_sound.mp3") 
                pygame.mixer.music.play() 
                self.taskExecution()

        except Exception:
            print("Say that again please...")
            return "none"

    def takeCommand(self):
        """Listen for a command from the user."""
        try:
            r = sr.Recognizer()
            with sr.Microphone() as mic:
                print("Listening...")
                r.pause_threshold = 1
                audio = r.listen(mic, timeout=2, phrase_time_limit=5)
                print("Recognizing...")
                query = r.recognize_google(audio, language='en-in').lower()
                print(f"User said: {query}")
                return query
        except Exception:
            print("Say that again please...")
            return "none"
        
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

    def taskExecution(self):
        """Execute tasks based on user commands."""
        # wish()
        while True:
            query = self.takeCommand()
            if query == "none":
                continue
            self.handleCommand(query)

    def handleCommand(self, query):
        """Handle different commands issued by the user."""
        result = chain.invoke({"context": self.context,"query": query})
        # print(f"Jarvis : {result}")
        speak(result)
        self.context += f"\n User: {query}\n Jarvis: {result}"

        if "website" or "wikipedia" in result:
            result = result.split("|")
            self.search_google(result[0])
        
        elif "youtube" in result:
            result = result.split('|')
            # print(result)
            kit.playonyt(result[-2])
            print(result[-2])
            speak(f"Playing {result[-2]} on Youtube")

    def makeCall(self, query):
        """Make a video or audio call using WhatsApp."""
        search = query.replace("video call", "").replace("call", "").replace("jarvis", "").strip()
        os.startfile("C:\\Program Files\\WindowsApps\\5319275A.WhatsAppDesktop_2.2212.8.0_x64__cv1g1gvanyjgm\\app\\Whatsapp")
        time.sleep(1)
        pyautogui.click(x=100, y=135)  # Adjust these coordinates based on your screen
        time.sleep(1)
        pyautogui.write(search)
        time.sleep(1)
        pyautogui.press('enter')
        time.sleep(1)
        pyautogui.click(x=1660, y=75)  # Adjust for video call; might need to change x-coordinate
        speak(f"Calling {search}")


class Main(QMainWindow):
    def __init__(self):
        super().__init__()
        self.ui = Ui_MainWindow()
        self.ui.setupUi(self)
        self.startTask()
        self.ui.pushButton.clicked.connect(self.computer_info)
        self.ui.play.clicked.connect(self.play_song)
        self.ui.next.clicked.connect(self.next_song)
        self.ui.connect.clicked.connect(self.connect_to_suit)

    def startTask(self):
        """Start the task execution and animations."""
        self.ui.movie = QtGui.QMovie("main.gif")
        self.ui.gif.setMovie(self.ui.movie)
        self.ui.movie.start()
        
        self.ui.stark_gif.setMovie(QMovie("suit_up.gif"))
        self.ui.movie.start()
        
        timer = QTimer(self)
        timer.timeout.connect(self.showTime)
        timer.start(1000)
        startExecution.start()
        self.showTime()

    def computer_info(self):
        """Display system information like CPU and RAM usage."""
        cpu = str(psutil.cpu_percent(4))
        ram = str(psutil.virtual_memory()[2])
        self.ui.cpu_precent.setText(cpu)
        self.ui.ram_percent.setText(ram)

        wifi = subprocess.check_output(['netsh', 'WLAN', 'show', 'interfaces']).decode('utf-8')
        if "Rathod's" in wifi:
            self.ui.wifi.setText("Rathod's")
        elif "Sarla" in wifi:
            self.ui.wifi.setText("Sarla")
        elif "Galaxy A50sF418" in wifi:
            self.ui.wifi.setText("Galaxy A50sF418")
        else:
            self.ui.wifi.setText("Unknown Network or Not Connected")
            self.ui.wifi.setIcon(QtGui.QIcon("wifi-not-connected.png"))

    def next_song(self):
        """Play a random song from the specified directory."""
        pygame.mixer.init()
        song_path = "C:\\Users\\siddh\\OneDrive\\Documents\\Songs"
        song = random.choice(os.listdir(song_path))
        pygame.mixer.music.load(os.path.join(song_path, song))
        pygame.mixer.music.play()
        self.ui.play.setIcon(QtGui.QIcon("pause.png"))
        self.ui.song_name.setText(song.replace(".mp3", ""))

    def play_song(self):
        """Toggle play/pause functionality for the music."""
        pygame.mixer.init()
        song_path = "C:\\Users\\siddh\\OneDrive\\Documents\\Songs"
        song = random.choice(os.listdir(song_path))

        if self.ui.song_name.text() == "No Song Playing Currently...":
            pygame.mixer.music.load(os.path.join(song_path, song))
            pygame.mixer.music.play()
            self.ui.play.setIcon(QtGui.QIcon("pause.png"))
            self.ui.song_name.setText(song.replace(".mp3", ""))
        else:
            pygame.mixer.music.pause()
            self.ui.play.setIcon(QtGui.QIcon("play.png"))
            self.ui.song_name.setText("No Song Playing Currently...")

    def connect_to_suit(self):
        """Connect to a remote system via SSH."""
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
        self.ui.status.setStyleSheet("background-color: rgb(0, 255, 0); font: 8pt 'Eras Medium ITC'; border: 2px Solid Black;")

    def showTime(self):
        """Display the current time and date."""
        current_time = QTime.currentTime().toString('hh:mm:ss')
        current_date = QDate.currentDate().toString(Qt.ISODate)
        self.ui.date.setText(current_date)
        self.ui.time.setText(current_time)

# Application execution
app = QApplication(sys.argv)
startExecution = MainThread()  # Initialize MainThread
jarvis = Main()  # Create main window instance
jarvis.show()  # Show the GUI
exit(app.exec_())  # Start the application event loop
