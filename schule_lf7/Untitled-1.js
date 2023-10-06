/*
id	timestamp            pegel
1	2023-10-05 12:41:00	18.000
2	2023-10-05 12:42:00	0.000
3  2023-10-05 12:43:00  78.500
4	2023-10-05 12:44:00	0.000
5	2023-10-05 12:45:00	0.000
6	2023-10-06 09:20:00	0.000
7	2023-10-06 09:21:00	0.000
8	2023-10-06 09:22:00	0.000
*/
// ----------

// Change these settings to change the display for different parts of the X axis
// grid configuration
const DISPLAY = true;
const BORDER = true;
const CHART_AREA = true;
const TICKS = true;

const config = {
  type: "line",
  data: data,
  options: {
    responsive: true,
    plugins: {
      title: {
        display: true,
        text: "Grid Line Settings",
      },
    },
    scales: {
      x: {
        border: {
          display: BORDER,
        },
        grid: {
          display: DISPLAY,
          drawOnChartArea: CHART_AREA,
          drawTicks: TICKS,
        },
      },
      y: {
        border: {
          display: false,
        },
        grid: {
          color: function (context) {
            if (context.tick.value > 0) {
              return Utils.CHART_COLORS.green;
            } else if (context.tick.value < 0) {
              return Utils.CHART_COLORS.red;
            }

            return "#000000";
          },
        },
      },
    },
  },
};
