import sys
import yfinance as yf
import matplotlib.pyplot as plt
import os
import pandas as pd
from datetime import datetime, timedelta
from sklearn.linear_model import LinearRegression
import numpy as np

try:
    ticker = sys.argv[1]
    period = sys.argv[2]
    strategy = sys.argv[3]
except IndexError:
    print("Error: Missing arguments. Usage: script.py <ticker> <period> <strategy>")
    sys.exit(1)

try:
    # Set the end date and calculate the start date based on the period
    end_date = datetime.now()
    if period == '1mo':
        start_date = end_date - timedelta(days=30)
    elif period == '3mo':
        start_date = end_date - timedelta(days=90)
    elif period == '6mo':
        start_date = end_date - timedelta(days=180)
    elif period == '1y':
        start_date = end_date - timedelta(days=365)
    else:
        print(f"Unknown period '{period}'.")
        sys.exit(1)

    # Fetch stock data
    stock_data = yf.download(ticker, start=start_date, end=end_date)

    if stock_data.empty:
        raise ValueError(f"No data found for ticker '{ticker}'.")

    # Predict future stock prices using Linear Regression
    def predict_future_prices(data, days_ahead=30):
        # Use 'Close' price for prediction
        data['Date'] = data.index
        data['Date'] = data['Date'].map(lambda x: x.toordinal())  # Convert dates to ordinal format

        X = np.array(data['Date']).reshape(-1, 1)
        y = data['Close'].values

        # Fit the linear regression model
        model = LinearRegression()
        model.fit(X, y)

        # Predict future dates
        future_dates = [end_date + timedelta(days=i) for i in range(1, days_ahead + 1)]
        future_dates_ordinal = np.array([date.toordinal() for date in future_dates]).reshape(-1, 1)

        # Predict future stock prices
        future_prices = model.predict(future_dates_ordinal)
        return future_dates, future_prices

    # Visualizing strategies and predictions
    plt.figure(figsize=(12, 6))

    if strategy == "moving_avg":
        stock_data['Short_MA'] = stock_data['Close'].rolling(window=20).mean()
        stock_data['Long_MA'] = stock_data['Close'].rolling(window=50).mean()
        plt.plot(stock_data['Close'], label='Close Price', color='blue')
        plt.plot(stock_data['Short_MA'], label='Short MA (20)', color='green')
        plt.plot(stock_data['Long_MA'], label='Long MA (50)', color='red')
        plt.title(f"{ticker} - Moving Averages")
        plt.legend()

    elif strategy == "rsi":
        delta = stock_data['Close'].diff(1)
        gain = delta.where(delta > 0, 0)
        loss = -delta.where(delta < 0, 0)
        avg_gain = gain.rolling(window=14).mean()
        avg_loss = loss.rolling(window=14).mean()
        rs = avg_gain / avg_loss
        stock_data['RSI'] = 100 - (100 / (1 + rs))
        plt.plot(stock_data['RSI'], label='RSI', color='purple')
        plt.axhline(30, linestyle='--', color='green', label='Oversold')
        plt.axhline(70, linestyle='--', color='red', label='Overbought')
        plt.title(f"{ticker} - RSI")
        plt.legend()

    else:
        print(f"Unknown strategy '{strategy}'. Please choose a valid strategy.")
        sys.exit(1)

    # Predict future stock prices for the next 30 days (or a customizable range)
    future_dates, future_prices = predict_future_prices(stock_data, days_ahead=30)
    plt.plot(future_dates, future_prices, label='Predicted Future Prices', color='orange', linestyle='--')

    # Save the plot
    output_path = "output_graph.png"
    if os.path.exists(output_path):
        os.remove(output_path)
    plt.savefig(output_path)
    plt.close()

    # Provide a simple Buy/Sell recommendation based on predicted prices
    if future_prices[-1] > future_prices[0]:
        recommendation = "Recommendation: Buy"
    else:
        recommendation = "Recommendation: Sell"

    # Output only the recommendation (no other details)
    print(recommendation)

except Exception as e:
    print(f"An error occurred: {e}")
