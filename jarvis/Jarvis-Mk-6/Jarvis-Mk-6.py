#? I have suggested changes at various places using comments starting with #?

#? Ideas that could be implemented (for the final demo day, if not now):-
#? Try to remove all hard-coded stuffs as it can raise error
#? For all queries executed by jarvis which later turn out to be unintended, delete that query-responses from the context.
#? For simple tasks like calling, playing music, or weather, which have a definite response already defined, don't send the prompt to LLM. Instead, make jarvis itself recognize the user need and perform the action.

import serial
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
from collections import deque
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
engine = pyttsx3.init()
voices = engine.getProperty('voices')
engine.setProperty('voices', voices[0].id)
#? Improve template and experiment with various prompts. Keep it as short as possible.
#? Instead of telling you are JARVIS from Ironman, just enter that you are a smart assistant named after Ironman's assistant.
template = """
You are a smart assistant named JARVIS (Just A Rather Very Intelligent System) after the Iron Man's assistant.

Don't bet bothered by User: and Jarvis: prompts. They are just to tell you about what was the conversation history till now. If you don't feel that it is relevant, you can ignore it. Don't mention the conversation history unless explicitly mentioned to do so.

If it seems as if the user is asking for link to a particular website your response should be strictly of the below format and nothing else:
Name of website | URL of website | website
For example,
Wikipedia | www.wikipedia.com | website

If it seems as if the user wanted to open a particular thing on youtube or you think that the name is of a song or it is explicitly mentioned to play a song on youtube, your response should be strictly of the below format and nothing else:
Name of the song | youtube
For example,
Tu Meri | youtube

If it seems as if the user wants to turn on or off bulb number 1 or 2, your output should be strictly of the format:
Turn [operation] Bulb [Number]
For example,
Turn on Bulb 1

Otherwise, if it seems that the user wants nothing as mentioned above, behave normally like Jarvis.

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
        speak(f"Good Morning, It's {strTime}")
    elif hour < 18:
        speak(f"Good Afternoon, It's {strTime}")
    else:
        speak(f"Good Evening, It's {strTime}")

def weatherReport():    #? Not being used in the code
    """Fetch and report the current weather."""
    ipAdd = requests.get('https://api.ipify.org').text
    url = f'https://get.geojs.io/v1/ip/geo/{ipAdd}.json'
    geo_requests = requests.get(url)
    geo_data = geo_requests.json()
    city = geo_data['city']

    user_api = '294bc099cbca8f73ec6d6c7ccc8b8818'
    link = f"https://api.openweathermap.org/data/2.5/weather?q={city}&appid={user_api}"
    api_data = requests.get(link).json()

    if api_data['cod'] == '404':
        speak("Sorry, couldn't fetch data")
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
        speak(f"Current temperature is: {temp_city:.2f} degree C")  #? Can we improve so that it speaks celsius but prints only C?
        speak(f"Current weather description: {weather_desc}")
        speak(f"Current wind speed: {wind_spd} km/h")

def control_bulb(ser, bulb_number, state):
    if bulb_number == 1:
        command = '1' if state == 'on' else '2'
    elif bulb_number == 2:
        command = '3' if state == 'on' else '4'
    ser.write(command.encode())

class MainThread(QThread):
    def __init__(self):
        super(MainThread, self).__init__()
        self.context = deque(maxlen=10) # mention in template that you will be given a deque of tuples containing (user_query,jarvis_response) in chronological order as context

    def run(self):
        """Start executing tasks."""
        wish()
        while True:
            self.wakeWord()

    def wakeWord(self):
        """Will call the takeCommand function only when it hears the wakeword."""
        try:
            r = sr.Recognizer()
            with sr.Microphone() as mic:
                print("Listening for wakeword...")
                r.pause_threshold = 1
                audio = r.listen(mic, timeout=2, phrase_time_limit=3)
                query = r.recognize_google(audio, language='en-in').lower()
                if 'jarvis' in query:
                    print(f"User said: {query}")
                    pygame.mixer.init() 
                    pygame.mixer.music.load("wake_word_sound.mp3") 
                    pygame.mixer.music.play() 
                    self.taskExecution()

        except Exception:
            print("Say that again please...")

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
            return None
        
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
        while True:
            query = self.takeCommand()
            if query:
                if "video call" in query:
                    if "jarvis" in query:
                        query2 = query.replace("jarvis", "")
                        query1 = query2.replace("video", "")
                        search = query1.replace("call", "")
                    else:
                        query1 = query.replace("video", "")
                        search = query1.replace("call", "")
                    pyautogui.click(x=739, y=1043)

                    time.sleep(1)
                    pyautogui.click(x=274, y=149)
                    time.sleep(1)
                    pyautogui.write(search)
                    time.sleep(1)
                    pyautogui.press('enter')
                    time.sleep(1)
                    pyautogui.click(x=310, y=223)
                    time.sleep(1)
                    pyautogui.click(x=1764, y=92)
                    speak(f"Calling {search}")
                elif "call" in query:
                    if "jarvis" in query:
                        query2 = query.replace("jarvis", "")
                        query1 = query2.replace("video", "")
                        search = query1.replace("call", "")
                    
                    else:
                        query1 = query.replace("video", "")
                        search = query1.replace("call", "")
                    pyautogui.click(x=739, y=1043)

                    time.sleep(1)
                    pyautogui.click(x=274, y=149)
                    time.sleep(1)
                    pyautogui.write(search)
                    time.sleep(1)
                    pyautogui.press('enter')
                    pyautogui.click(x=310, y=223)
                    time.sleep(0.1)
                    pyautogui.click(x=1823, y=90)
                    speak(f"Calling {search}")
                else:
                    self.handleCommand(query)

    def handleCommand(self, query):
        """Handle different commands issued by the user."""
        context=''
        for i in self.context:
            context+='\nUser: '+i[0]+'\nJarvis: '+i[1]
        result = chain.invoke({"context": context,"query": query})
        print(f"Jarvis : ", end='')
        speak(result)
        self.context.append((query,result))

        if result[-7:] == "website":
            result = result.split("|")
            self.search_google(result[1])
        
        elif result[-7:] == "youtube":
            result = result.split('|')
            kit.playonyt(result[0])
            speak(f"Playing {result[0]} on Youtube")

        elif "Turn" in result and "Bulb" in result:
            state = 'on' if 'on' in result else 'off'
            bulb = int(result[-1])
            ser = serial.Serial('COM8', 9600)
            time.sleep(2)
            control_bulb(ser, bulb, state)

    def makeCall(self, query):  #? This is not being used in the code. And don't hard-code this
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
