import mysql.connector
import matplotlib.pyplot as plt
import seaborn as sns
from datetime import datetime, timedelta

# MySQL connection
conn = mysql.connector.connect(
    host="localhost",
    user="root",
    password="",
    database="UST"
)
cursor = conn.cursor()

# Replace with the actual logged-in user's mobile
mobile = '8008389436'

# 1. Fetch Monthly Spending
cursor.execute("""
    SELECT DATE_FORMAT(order_date, '%Y-%m') AS order_month, SUM(price) AS total_spent
    FROM order_items
    WHERE mobile = %s AND status NOT IN ('Cancelled')
    GROUP BY order_month
    ORDER BY order_month ASC
""", (mobile,))
monthly_data = cursor.fetchall()

labels = [row[0] for row in monthly_data]
spending = [float(row[1]) for row in monthly_data]

# Predict next month if 3+ months of data
if len(spending) >= 3:
    prediction = round(sum(spending[-3:]) / 3, 2)
    last_date = datetime.strptime(labels[-1] + '-01', '%Y-%m-%d')
    next_month = (last_date + timedelta(days=31)).strftime('%Y-%m')
    labels.append(f"{next_month} (Predicted)")
    spending.append(prediction)

# 2. Fetch Category-wise Spending
cursor.execute("""
    SELECT category, SUM(price) AS total
    FROM order_items
    WHERE mobile = %s AND status NOT IN ('Cancelled')
    GROUP BY category
""", (mobile,))
category_data = cursor.fetchall()

category_labels = [row[0] for row in category_data]
category_values = [float(row[1]) for row in category_data]

# Close MySQL connection
cursor.close()
conn.close()

# 3. Plot Monthly Spending Bar Chart
plt.figure(figsize=(10, 6))
colors = sns.color_palette('husl', len(spending))
bars = plt.bar(labels, spending, color=colors)
plt.title('Monthly Spending (with Prediction)', fontsize=16)
plt.ylabel('Amount Spent ($)')
plt.xlabel('Month')
plt.xticks(rotation=45)
plt.tight_layout()

# Add values on bars
for bar in bars:
    height = bar.get_height()
    plt.text(bar.get_x() + bar.get_width() / 2, height + 5, f"${height:.2f}", ha='center')

plt.show()

# 4. Plot Category-wise Pie Chart
plt.figure(figsize=(7, 7))
plt.pie(category_values, labels=category_labels, autopct='%1.1f%%', startangle=140, colors=sns.color_palette('pastel'))
plt.title('Spending by Category')
plt.axis('equal')  # Keep pie circular
plt.show()
