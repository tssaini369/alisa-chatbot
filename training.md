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

---------------------------------------------------------------------------------------------------------------------------------

📊 Format for .csv Upload
If you're using a .csv file, simply replace the | with a comma (,):

Example (training.csv):

Hello,Hello! Need assistance with something?

Good morning,Good morning! How can I support you today?

Do you offer support on weekends?,Our support team is available Monday to Saturday,9 AM to 6 PM.

⚠️ Note: For CSV files, avoid extra commas inside a single message unless properly wrapped in quotes.

---------------------------------------------------------------------------------------------------------------------------------
📥 How to Upload
Go to Train Chatbot section in the plugin settings.
Choose either manual input or upload a .txt / .csv file (available for licensed users).
Save your changes, and Alisa will be instantly updated with the new training data.
