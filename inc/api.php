<?php
function alisa_get_response($message) {
    // Basic response logic
    $message = strtolower(trim($message));
    
    // Get training data
    $training_data = get_option('alisa_chatbot_training_data', '');
    if (!empty($training_data)) {
        $qa_pairs = explode("\n", $training_data);
        foreach ($qa_pairs as $pair) {
            $qa = explode("|", $pair);
            if (count($qa) == 2) {
                list($question, $answer) = $qa;
                if (stripos(trim($question), $message) !== false) {
                    return trim($answer);
                }
            }
        }
    }
    
    return "I'm still learning. Could you please rephrase your question?";
}
?>