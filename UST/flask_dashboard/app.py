from flask import Flask, render_template, request
import mysql.connector
import matplotlib.pyplot as plt
import seaborn as sns
import os
from datetime import datetime, timedelta

app = Flask(__name__)

def get_connection():
    return mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="UST"
    )

def generate_charts(mobile):
    conn = get_connection()
    cursor = conn.cursor()

    # Monthly Spending (Bar Chart)
    cursor.execute("""
        SELECT DATE_FORMAT(order_date, '%Y-%m') AS order_month, SUM(price)
        FROM order_items
        WHERE mobile = %s AND status NOT IN ('Cancelled')
        GROUP BY order_month ORDER BY order_month ASC
    """, (mobile,))
    rows = cursor.fetchall()
    labels = [row[0] for row in rows]
    spending = [float(row[1]) for row in rows]

    if len(spending) >= 3:
        prediction = round(sum(spending[-3:]) / 3, 2)
        last_month = datetime.strptime(labels[-1] + "-01", "%Y-%m-%d")
        next_month = (last_month + timedelta(days=31)).replace(day=1).strftime("%Y-%m")
        labels.append(f"{next_month} (Predicted)")
        spending.append(prediction)

    os.makedirs('static/charts', exist_ok=True)
    plt.figure(figsize=(10, 6))
    colors = sns.color_palette('husl', len(spending))
    bars = plt.bar(labels, spending, color=colors)
    plt.title("Monthly Spending", fontsize=16)
    plt.ylabel("Amount Spent ($)")
    plt.xticks(rotation=45)
    for bar in bars:
        yval = bar.get_height()
        plt.text(bar.get_x() + bar.get_width() / 2, yval + 5, f"${yval:.2f}", ha='center')
    plt.tight_layout()
    plt.savefig("static/charts/monthly_spending.png")
    plt.close()

    # Category Pie Chart
    fixed_categories = ['Home', 'Fashion', 'Beauty', 'Electronics', 'Health']
    fixed_colors = {
        'Home': 'purple',
        'Fashion': 'green',
        'Beauty': '#99FF99',
        'Electronics': '#FFCC99',
        'Health': '#C2C2F0',
        'Others': '#CCCCCC'
    }

    cursor.execute("""
        SELECT category, SUM(price)
        FROM order_items
        WHERE mobile = %s AND status NOT IN ('Cancelled')
        GROUP BY category
    """, (mobile,))
    raw_data = cursor.fetchall()

    cat_labels = []
    cat_values = []
    cat_colors = []
    explode = []
    others = 0

    for cat, val in raw_data:
        val = float(val)
        if cat in fixed_categories:
            cat_labels.append(cat)
            cat_values.append(val)
            cat_colors.append(fixed_colors[cat])
            explode.append(0.05)
        else:
            others += val

    if others > 0:
        cat_labels.append("Others")
        cat_values.append(others)
        cat_colors.append(fixed_colors["Others"])
        explode.append(0.05)

    if not cat_values:
        plt.figure(figsize=(8, 6))
        plt.text(0.5, 0.5, 'No category spending data available', ha='center', va='center', fontsize=14)
        plt.axis('off')
    else:
        plt.figure(figsize=(8, 8))
        wedges, texts, autotexts = plt.pie(
            cat_values,
            labels=cat_labels,
            colors=cat_colors,
            autopct=lambda pct: f"${(pct / 100) * sum(cat_values):.2f}",
            explode=explode,
            startangle=140,
            shadow=True,
            wedgeprops={'edgecolor': 'black'}
        )
        plt.legend(wedges, cat_labels, title="Categories", loc="center left", bbox_to_anchor=(1, 0, 0.5, 1))
        plt.title("Spending by Category")
        plt.axis("equal")

    plt.tight_layout()
    plt.savefig("static/charts/category_pie.png", bbox_inches='tight')
    plt.close()

    cursor.close()
    conn.close()

@app.route('/')
def dashboard():
    mobile = request.args.get('mobile')
    if not mobile:
        return "Mobile number not provided. Please log in from the main portal.", 400

    conn = get_connection()
    cursor = conn.cursor(dictionary=True)

    # User info
    cursor.execute("SELECT username, email FROM login WHERE phone_number = %s", (mobile,))
    user = cursor.fetchone()
    if not user:
        return "User not found.", 404

    # Total orders
    cursor.execute("""
        SELECT COUNT(*) AS total_orders
        FROM order_items
        WHERE mobile = %s AND status NOT IN ('Cancelled')
    """, (mobile,))
    total_orders = cursor.fetchone()['total_orders']

    today = datetime.now()
    first_day = today.replace(day=1)
    next_month = (first_day + timedelta(days=32)).replace(day=1)

    # Monthly Spending
    cursor.execute("""
        SELECT SUM(price) AS monthly_spending
        FROM order_items
        WHERE mobile = %s AND status NOT IN ('Cancelled') AND order_date >= %s AND order_date < %s
    """, (mobile, first_day.strftime('%Y-%m-%d'), next_month.strftime('%Y-%m-%d')))
    monthly_spending = cursor.fetchone()['monthly_spending'] or 0

    # Yearly Spending
    year_start = datetime(today.year, 1, 1)
    next_year_start = datetime(today.year + 1, 1, 1)
    cursor.execute("""
        SELECT SUM(price) AS yearly_spending
        FROM order_items
        WHERE mobile = %s AND status NOT IN ('Cancelled') AND order_date >= %s AND order_date < %s
    """, (mobile, year_start.strftime('%Y-%m-%d'), next_year_start.strftime('%Y-%m-%d')))
    yearly_spending = cursor.fetchone()['yearly_spending'] or 0

    generate_charts(mobile)

    cursor.close()
    conn.close()

    return render_template(
        "dashboard.html",
        user=user,
        mobile=mobile,
        total_orders=total_orders,
        monthly_spending=monthly_spending,
        yearly_spending=yearly_spending
    )

if __name__ == '__main__':
    app.run(debug=True)
