import sLIntell as sil
import time
import playsound
import webbrowser
start = input(">>")
if start=="start":
    time.sleep(15)
    playsound.playsound("Alarm Tone.mp3")
    sil.speak("Sir, you need to wake up")
    time.sleep(4)
    sil.speak("No sir, you have already slept through your morning workout session.")
    time.sleep(3)
    sil.speak("Sir, if you don't wake up right now I will tell your girlfriend that it was me who messaged her happy birthday last night at 12 AM")
    time.sleep(2)
    sil.speak("Glad you woke up")
    time.sleep(6)
    sil.speak("Done")
    sil.speak("Sir, I am also feeling a bit dizzy but I think we were working on making a helicopter to fly to london")
    time.sleep(2)
    sil.speak("Sorry sir, you were studying thermodynamics last night")
    time.sleep(4)
    sil.speak("Which song should I play?")
    time.sleep(2)
    sil.speak("Okk sir")
    webbrowser.open("https://www.youtube.com/watch?v=zAlX1V3lK5s&t=5s")
    

    