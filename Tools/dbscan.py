print(__doc__)
import sys
import numpy as np

from sklearn.cluster import DBSCAN
from sklearn import metrics
from sklearn.datasets.samples_generator import make_blobs
from sklearn.preprocessing import StandardScaler


##############################################################################
# Read Turker data file
def read_turk_data(filename):
    data = {}
    f = open(filename)
    lines = f.readlines()
    for line in lines:
        split_str = line.split('\t\t')
        #print split_str
        datum = {}
        datum["id"] = split_str[0]
        datum["workerid"] = split_str[1]
        datum["vid"] = split_str[2][:-4] # e.g., "s1_c02_v02" and "s04" from "s1_c02_v02_s04"
        datum["labels"] = parse_labels(datum["workerid"], int(split_str[2][-2:]), split_str[3].rstrip('\n'))
        if (datum["vid"] == "s1_p03_v03"):
            print datum
        if datum["vid"] not in data:
            data[datum["vid"]] = []
        for label in datum["labels"]:
            data[datum["vid"]].append(label)
    f.close()
    return data

##############################################################################
# Read ground truth file
def read_truth_data(filename):
    data = {}
    f = open(filename)
    lines = f.readlines()
    for line in lines:
        split_str = line.split('\t\t')
        #print split_str
        datum = {}
        datum["id"] = split_str[0]
        datum["vid"] = split_str[1]
        datum["labels"] = parse_labels("", 1, split_str[2].rstrip('\n'))
        #print datum
        if datum["vid"] not in data:
            data[datum["vid"]] = []
        for label in datum["labels"]:
            data[datum["vid"]].append(label)
    f.close()
    print data
    return data

##############################################################################
# Parse labels
def parse_labels(workerid, seq, str):
    labels = []
    #print str
    split_str = str.split('\t')
    #print split_str
    for v in split_str:
        if v == '': continue
        split_v = v.split('@')
        label = {}
        label["workerid"] = workerid
        label["time"] = (seq-1)*54 + float(split_v[0])
        label["desc"] = split_v[1]
        labels.append(label)
    return labels

##############################################################################
# Generate sample data
#centers = [[1, 1], [-1, -1], [1, -1]]
#X, labels_true = make_blobs(n_samples=20, n_features=1, cluster_std=0.4, random_state=0)

def format_data(label_list):
    #data = [[10.67, 18, 51, 9, 51, 10.2, 19, 51],]
    #print label_list
    data = [label_list,]
    X = np.empty( shape=(len(label_list), 1) )
    X = np.transpose(data)
    #print X
    #print X.shape
    X = StandardScaler().fit_transform(X)
    #print X
    return X

##############################################################################
# Compute DBSCAN
def run_dbscan(labels_turk, label_turk_list, label_truth_list, epsilon):
    db = DBSCAN(eps=epsilon, min_samples=2, metric="euclidean").fit(labels_turk)
    core_samples = db.core_sample_indices_
    labels = db.labels_

    # Number of clusters in labels, ignoring noise if present.
    n_clusters_ = len(set(labels)) - (1 if -1 in labels else 0)    
    #print labels
    print core_samples

    print('Estimated number of clusters: %d' % n_clusters_)
    # print("Homogeneity: %0.3f" % metrics.homogeneity_score(labels_truth, labels))
    # print("Completeness: %0.3f" % metrics.completeness_score(labels_truth, labels))
    # print("V-measure: %0.3f" % metrics.v_measure_score(labels_truth, labels))
    # print("Adjusted Rand Index: %0.3f"
    #       % metrics.adjusted_rand_score(labels_truth, labels))
    # print("Adjusted Mutual Information: %0.3f"
    #       % metrics.adjusted_mutual_info_score(labels_truth, labels))
    # print("Silhouette Coefficient: %0.3f"
    #       % metrics.silhouette_score(X, labels))
    return labels

##############################################################################
# Plot result

def plot_results(labels):
    import pylab as pl

    # Black removed and is used for noise instead.
    unique_labels = set(labels)
    colors = pl.cm.Spectral(np.linspace(0, 1, len(unique_labels)))
    for k, col in zip(unique_labels, colors):
        if k == -1:
            # Black used for noise.
            col = 'k'
            markersize = 6
        class_members = [index[0] for index in np.argwhere(labels == k)]
        cluster_core_samples = [index for index in core_samples
                                if labels[index] == k]
        for index in class_members:
            x = X[index]
            if index in core_samples and k != -1:
                markersize = 14
            else:
                markersize = 6
            pl.plot(x[0], x[1], 'o', markerfacecolor=col,
                    markeredgecolor='k', markersize=markersize)

    #pl.title('Estimated number of clusters: %d' % n_clusters_)
    #pl.show()



def getClusterTime(cluster_data, cluster_id):
    total_time = 0
    count = 0
    #print cluster_data
    for item in cluster_data:
        #print item
        if (cluster_id == item["cluster_id"]):
            total_time += item["time"]
            count = count + 1
    if count == 0:
        return -1
    return total_time / count



''' Usage python dbscan.py [turk_filename] [truth_filename]
    Parameters
    - turk_filename:
    - truth_filename:
'''
turk_filename = sys.argv[1]
truth_filename = sys.argv[2]
# read and parse data
turk = read_turk_data(turk_filename)
truth = read_truth_data(truth_filename)
vidlist = turk.keys()
vidlist.sort()

for i in range(1, 30):
    eps = i * 0.01
    final_data = {}
    for vid in vidlist:
        print "\n" 
        sorted_turk = sorted(turk[vid], key=lambda tup: tup["time"])

        # if (vid == "s1_p03_v03"):
        #     print "ORG:"
        #     for label in turk[vid]:
        #         print label["time"], label["desc"]
        #     print "SORTED:"
        #     for label in sorted_turk:
        #         print label["time"], label["desc"]

        print vid, "Turk:", len(turk[vid]), "Truth:", len(truth[vid])#, data[vid]
        # only grab time information from the data
        label_turk_list = []
        label_truth_list = []
        for label in turk[vid]:
            label_turk_list.append(label["time"])
        for label in truth[vid]:
            label_truth_list.append(label["time"])
        label_turk_list.sort()
        label_truth_list.sort()
        # format for DBSCAN
        
        labels_turk = format_data(label_turk_list)
        #labels_truth = label_truth_list #format_data(label_truth_list)
        # now run DBSCAN
        clusters = run_dbscan(labels_turk, label_turk_list, label_truth_list, eps)


        # if (vid == "s1_p03_v03"):
        print "TRUE:"
        for label in truth[vid]:
            print label["time"], label["desc"]
        print "==="
        print "TURK:"
        cluster_data = {}
        for (i, label) in enumerate(label_turk_list):
            key = str(clusters[i])
            entry = {'cluster_id': key, 'time': label, 'workerid': sorted_turk[i]["workerid"], 'desc': sorted_turk[i]["desc"]}
            if key in cluster_data:
                cluster_data[key].append(entry)
            else:
                cluster_data[key] = []
            #print cluster_data[key]
            print label, "(", int(clusters[i]), ")", sorted_turk[i]["workerid"], sorted_turk[i]["desc"]

        final_data[vid] = {}
        for cid in cluster_data:
            #print cid, cluster_data[cid]
            final_data[vid][cid] = {'cluster_id': cid, 'time': getClusterTime(cluster_data[cid], cid), 'points_turk': cluster_data[cid]}
            print "---"
            print final_data[vid][cid]
        # for cluster in cluster_data:
        #     final_data[vid] = {'cluster_id': cluster["cluster_id"], 'time': getClusterTime(cluster), 'points_turk': cluster}

        import json
        with open(sys.argv[1] + '.' + str(eps) + '.final.json', 'wb') as fp:
            json.dump(final_data, fp)




