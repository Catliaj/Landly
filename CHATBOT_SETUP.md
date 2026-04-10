# Landly AI Chatbot Setup Guide

## ✅ Implementation Complete

I've successfully implemented the Landly AI Chatbot with Google Gemini API integration. Here's what was done:

### Files Created/Modified:

1. **ChatbotController.php** (NEW)
   - Location: `app/Controllers/Buyer/ChatbotController.php`
   - Handles all chatbot API requests
   - Integrates with Google Gemini API
   - Searches listings from database
   - Suggests relevant properties to users

2. **Routes.php** (MODIFIED)
   - Added route: `POST /buyer/chatbot/send-message`
   - Maps to `ChatbotController::sendMessage()`

3. **Dashboard_Buyer.php** (MODIFIED)
   - Updated chatbot frontend with:
     - Real-time API integration
     - Listing card display with images
     - Clickable listing cards
     - Property details modal functionality
     - Loading states and error handling

### Features Implemented:

✅ **AI-Powered Responses**

- Uses Google Gemini API for intelligent responses
- Context-aware system prompt for Landly-specific guidance
- Only answers land-related questions

✅ **Smart Listing Search**

- Extracts keywords from user messages
- Searches database for relevant properties
- Falls back to random listings if no matches
- Shows up to 3 listings per response

✅ **Interactive Listing Cards**

- Displays property image, title, location, price, size
- Clickable cards to view full details
- Same detailed view as Browse Listings section
- Responsive design with hover effects

✅ **Conversation Features**

- User messages and bot responses styled differently
- Loading indicator while AI processes
- Error handling with user-friendly messages
- Proper session management

---

## 🔧 SETUP REQUIRED (You Need to Do This)

### Step 1: Add Environment Variable

Add your Google Gemini API key to your `.env` file:

```env
GEMINI_API_KEY=your_actual_gemini_api_key_here
GEMINI_MODEL=gemini-2.5-flash
```

**How to get a free Gemini API key:**

1. Go to https://ai.google.dev/
2. Click "Get API Key"
3. Sign in with your Google account
4. Create a new API key (FREE tier available)
5. Copy the key and paste it in your .env file

### Step 2: Verify Database Structure

Make sure your `land_listings` table has these columns:

- `listing_id` (primary key)
- `title` (property title)
- `description` (property details)
- `location` (property location)
- `price` (property price)
- `size` (property size in sqm)
- `primary_image_url` (featured image)
- `is_deleted` (soft delete flag)

These columns are required for chatbot listing search to work.

### Step 3: Test the Chatbot

1. Run `php spark serve` (if not already running)
2. Login as a buyer
3. Click the 💬 **LandlyBot** icon in the bottom-right corner
4. Type a message like:
   - "Show me properties in Nasugbu"
   - "I'm looking for affordable land"
   - "What's near Batangas?"
5. The chatbot will:
   - Respond with AI-generated answer
   - Suggest matching properties
   - Allow you to click listings to see details

---

## 📋 Example Conversations

### Example 1: Location Search

```
User: Show me properties in Nasugbu Batangas
Bot: I found several great properties in Nasugbu!
     Here are my top suggestions...
[Displays 3 clickable listing cards]
```

### Example 2: Price Range

```
User: Do you have affordable beachfront land?
Bot: Yes! I have some beautiful properties with beach access.
     Here are options that might interest you...
[Displays 3 clickable listing cards]
```

### Example 3: Off-topic

```
User: What's the weather today?
Bot: Sorry, I can only assist with land listings and
     property-related inquiries on Landly.
```

---

## 🎯 How It Works Behind the Scenes

1. **User sends message** → Chatbot input box
2. **JavaScript calls API** → `POST /buyer/chatbot/send-message`
3. **Controller extracts keywords** → "nasugbu", "beachfront", etc.
4. **Database search** → Finds matching properties
5. **Prompts Gemini** → AI generates contextual response
6. **Returns JSON** → Message + listing cards
7. **Frontend displays** → Bot message + clickable listings

---

## 🔒 Security Notes

- API key is stored safely in `.env` file (never exposed in code)
- API calls use cURL with SSL verification enabled
- User messages are sent to Gemini API (standard API behavior)
- Listings are filtered (only shows active listings)
- Input validation on all requests

---

## 🐛 Troubleshooting

### Chatbot says "API key is not configured"

→ Make sure GEMINI_API_KEY is added to .env file

### Chatbot won't respond

→ Check browser console (F12) for errors
→ Check `writable/logs/log*.log` for server errors

### No listings showing

→ Make sure database has properties with `is_deleted = 0`
→ Verify `primary_image_url` column is populated

### Long response times

→ Gemini API can take 2-5 seconds
→ This is normal, user sees loading indicator

---

## 📚 Available API Endpoints

```
POST /buyer/chatbot/send-message
├── Parameter: message (string, required)
├── Response: {
│   "status": "success",
│   "message": "Bot response text",
│   "listings": [
│       {
│           "id": 123,
│           "title": "Beautiful Beach Property",
│           "location": "Nasugbu, Batangas",
│           "price": 2500000,
│           "size": 1500,
│           "image": "https://..."
│       }
│   ]
│ }
```

---

## 🚀 Future Enhancements (Optional)

Consider adding these features later:

1. **Conversation history** - Save chat history per user
2. **Seller recommendations** - Show verified seller info
3. **Smart filters** - "Price between 2M-5M", "Minimum 1000 sqm"
4. **Property comparisons** - Compare multiple listings
5. **Geolocation integration** - "Show properties near me"
6. **Document suggestions** - Recommend legal documents to request

---

## ✨ That's It!

Your Landly AI Chatbot is now ready! 🎉

**Next Step:** Add your GEMINI_API_KEY to .env and test it out!

Questions? Check your server logs in `writable/logs/` for debugging.
