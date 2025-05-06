
window.addEventListener('load', function() {
    document.getElementById('generateBtn').click();
});

document.getElementById('generateBtn').addEventListener('click', function() {
    // Get today's date in DD/MM/YYYY format
    const today = new Date();
    const day = String(today.getDate()).padStart(2, '0');
    const month = String(today.getMonth() + 1).padStart(2, '0');
    const year = today.getFullYear();
    // const todayFormatted = `${day}/${month}/${year}`;
    const todayFormatted = `${day}/${month}/${year}`;
    
    // Indonesian date format
    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    const todayFormattedID = today.toLocaleDateString('id-ID', options);
    
    // Initialize output text
    let outputText = `*UNS HARI INI. ${todayFormattedID}*\n\n`;
    
    // Fetch data from Google Sheets
    fetch('https://docs.google.com/spreadsheets/d/e/2PACX-1vRlqimhvMaK2O_0zv6DNRcVGoXYah3jIlOOytSAEz-KE02CWtXauKAaG1GEnOcJENE-IT_FY5kEsiZv/pubhtml')
        .then(response => response.text())
        .then(html => {
            // Parse the HTML table
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const table = doc.querySelector('table');
            const rows = table.querySelectorAll('tr');
            
            // Arrays to store news items
            const newsID = [];
            const newsEN = [];
            
            // Process each row (skip header row)
            for (let i = 1; i < rows.length; i++) {
                const cells = rows[i].querySelectorAll('td');
                
                // Check for Indonesian news (columns I, F, L)
                if (cells.length > 8 && cells[8].textContent.trim() === todayFormatted) {
                    const title = cells[5]?.textContent.trim() || '';
                    const link = cells[11]?.textContent.trim() || '';
                    if (title && link) {
                        newsID.push({ title, link });
                    }
                }
                
                // Check for English news (columns Q, P, T)
                if (cells.length > 16 && cells[16].textContent.trim() === todayFormatted) {
                    const title = cells[15]?.textContent.trim() || '';
                    const link = cells[19]?.textContent.trim() || '';
                    if (title && link) {
                        newsEN.push({ title, link });
                    }
                }
            }
            
            // Add Indonesian news to output
            outputText += "*BERITA ID*\n";
            if (newsID.length === 0) {
                outputText += "Tidak ada berita hari ini.\n";
            } else {
                newsID.forEach((item, index) => {
                    outputText += `*${index + 1}. ${item.title}*\n${item.link}\n\n`;
                });
            }
            
            // Add English news to output
            outputText += "\n*BERITA EN*\n";
            if (newsEN.length === 0) {
                outputText += "No news today.\n";
            } else {
                newsEN.forEach((item, index) => {
                    outputText += `*${index + 1}. ${item.title}*\n${item.link}\n\n`;
                });
            }
            
            // Display the output in textarea
            document.getElementById('outputText').value = outputText;

        })
        .catch(error => {
            console.error('Error fetching data:', error);
            document.getElementById('outputText').value = "Error fetching data. Please try again later.";

        });
});

document.getElementById('copyBtn').addEventListener('click', function() {
    const outputText = document.getElementById('outputText');
    outputText.select();
    document.execCommand('copy');
    
    // Optional: Show a brief message that text was copied
    const originalText = this.textContent;
    this.textContent = 'Copied!';
    setTimeout(() => {
        this.textContent = originalText;
    }, 2000);
});
