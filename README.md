# NagaraLive
NagaraLive is a island wide real-time neighborhood intelligence and community coordination platform divided to each district that brings Events (tailored for Elderly, Children, and Youth), Public Notices, Weather Emergencies, and Power/Water Cutout updates into one clean feed.
To prevent misinformation and unauthorized tampering, any resident an post a new note or verify an alert, while public users cannot edit or delete existing posts-only Admins have full moderation rights to edit, resolve, or delete entries.
Al & Bonus Architecture:
* Google Gemini API (gemini-3.8-flash): Automatically translates
Sinhala/Singlish notes into clear English and generates a live Community Situation Brief summarizing active hazards and neighborhood events.
* cuCompanion ( cli.php): Allows low-bandwidth terminal queries ( php cli.php status /php cli.php brief) alongside the web dashboard.
Al Disclosure: Google Gemini was used for development assistance, and the Gemini API (gemini-3.8-fLash) runs live inside gemini php for translation and briefing generation. Google Cloud Project Number: [599221554770]
