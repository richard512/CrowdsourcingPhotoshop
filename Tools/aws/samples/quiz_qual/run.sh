#!/usr/bin/env sh
#
# Copyright 2012 Amazon Technologies, Inc.
# 
# Licensed under the Amazon Software License (the "License");
# you may not use this file except in compliance with the License.
# You may obtain a copy of the License at:
# 
# http://aws.amazon.com/asl
# 
# This file is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES
# OR CONDITIONS OF ANY KIND, either express or implied. See the
# License for the specific language governing permissions and
# limitations under the License.
 

cd ../..
cd bin
./createQualificationType.sh $1 $2 $3 $4 $5 $6 $7 $8 $9 -sandbox -question ../samples/quiz_qual/qualification.question -properties ../samples/quiz_qual/qualification.properties -answer ../samples/quiz_qual/qualification.answer

cd ..
cd samples/quiz_qual
