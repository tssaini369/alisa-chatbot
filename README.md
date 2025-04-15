# Alisa - AI Chatbot for WordPress

A customizable AI chatbot plugin with admin panel training, conversation storage, and licensing.

## Features

- Customizable chat interface
- Admin training panel
- Conversation storage
- Google Fonts integration
- Color customization options
- Responsive design

## Installation

1. Upload `alisa-chatbot` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Configure the chatbot in Settings > Alisa Chatbot

## Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- MySQL 5.6 or higher

## Usage

1. Go to Alisa Chatbot in WordPress admin
2. Configure appearance settings
3. Add training data
4. Customize chat messages

## License

Proprietary - All rights reserved

💡 What’s Next?
We’re working on a paid version that unlocks:

Bulk training via file upload (CSV, TXT)
Branding customization
Multi-language support
More powerful AI features

🧠 How to Train Alisa Chatbot
Alisa Chatbot supports manual training and bulk training via .txt or .csv file upload.

You can add questions and their responses to teach Alisa how to respond in a natural, conversational way.

📄 Format for Manual or .txt Upload
Each line represents a question and answer pair, separated by a pipe (|) symbol:

Question|Answer
You can also use multiple | within an answer to simulate pauses or sentence breaks.

Example:

Hello|Hello! Need assistance with something?

Good morning|Good morning! How can I support you today?

What is your refund policy?|You can request a refund within 7 days of purchase. Please check our full policy on the Refund page.

Do you offer support on weekends?|Our support team is available Monday to Saturday|9 AM to 6 PM.

Is the chatbot customizable?|Absolutely! You can change the bot’s name|personality, and even train it with your own data.

---------------------------------------------------------------------------------------------------------------------------------------------------

📊 Format for .csv Upload
If you're using a .csv file, simply replace the | with a comma (,):

Example (training.csv):

Hello,Hello! Need assistance with something?

Good morning,Good morning! How can I support you today?

Do you offer support on weekends?,Our support team is available Monday to Saturday,9 AM to 6 PM.

⚠️ Note: For CSV files, avoid extra commas inside a single message unless properly wrapped in quotes.

📥 How to Upload
Go to Train Chatbot section in the plugin settings.

Choose either manual input or upload a .txt / .csv file (available for licensed users).

Save your changes, and Alisa will be instantly updated with the new training data.
