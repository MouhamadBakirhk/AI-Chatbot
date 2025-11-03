from flask import Flask, request, jsonify
from flask_cors import CORS
import requests
import openai
import os
from dotenv import load_dotenv
import spacy
from difflib import SequenceMatcher

# Load environment variables
load_dotenv()
OPENROUTER_API_KEY = os.getenv("OPENROUTER_API_KEY")

# Configure OpenAI (OpenRouter)
openai.api_key = OPENROUTER_API_KEY
openai.api_base = "https://openrouter.ai/api/v1"
openai.api_type = "open_ai"

# Flask app
app = Flask(__name__)
CORS(app)

# Load spaCy NLP model
nlp = spacy.load("en_core_web_sm")

def extract_keywords(text):
    """Extract important keywords for fuzzy matching."""
    doc = nlp(text)
    return [token.lemma_.lower() for token in doc if token.is_alpha and not token.is_stop]

def similar(a, b):
    """Compute similarity between two strings."""
    return SequenceMatcher(None, a, b).ratio()

@app.route("/chatbot", methods=["POST"])
def chatbot():
    data = request.json
    user_message = data.get("message", "").strip()

    reply = None

    # 1️⃣ Fetch questions from Laravel DB
    try:
        response_db = requests.get("http://127.0.0.1:8000/api/chat").json()
        questions = [m['question'] for m in response_db]
        answers = [m['answer'] for m in response_db]

        user_keywords = extract_keywords(user_message)

        best_match = None
        highest_score = 0

        for idx, question in enumerate(questions):
            question_keywords = extract_keywords(question)
            # Keyword-based score
            score = len(set(user_keywords) & set(question_keywords)) / max(len(set(question_keywords)), 1)
            # Also consider fuzzy string similarity
            score += similar(user_message.lower(), question.lower())
            score /= 2  # average score

            if score > highest_score and score >= 0.5:  # cutoff 0.5
                highest_score = score
                best_match = idx

        if best_match is not None:
            reply = answers[best_match]

    except Exception as e:
        print("Error fetching chat from Laravel:", e)

    # 2️⃣ If no match, ask AI
    if not reply:
        try:
            completion = openai.ChatCompletion.create(
                model="deepseek/deepseek-chat-v3.1:free",
                messages=[
                    {"role": "system", "content": "Answer in English."},
                    {"role": "user", "content": user_message}
                ]
            )
            reply = completion.choices[0].message.content.strip()
        except Exception as e:
            reply = "Sorry, an error occurred while trying to answer. Please try again."

        # 3️⃣ Save new question + AI answer to DB
        try:
            requests.post("http://127.0.0.1:8000/api/chat", json={
                "question": user_message,
                "answer": reply
            })
        except Exception as e:
            print("Error saving chat to Laravel:", e)

    return jsonify({"reply": reply})

if __name__ == "__main__":
    app.run(port=5000, debug=True)
